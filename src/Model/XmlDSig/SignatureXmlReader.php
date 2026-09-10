<?php

namespace LightSaml\Model\XmlDSig;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Exception;
use LightSaml\Error\LightSamlSecurityException;
use LightSaml\Error\LightSamlXmlException;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LightSaml\SamlConstants;
use LogicException;
use RobRichards\XMLSecLibs\XMLSecEnc;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class SignatureXmlReader extends AbstractSignatureReader
{
    /** @var XMLSecurityDSig */
    protected $signature;

    /** @var string[] */
    protected $certificates = [];

    /**
     * @param string $certificate
     */
    public function addCertificate($certificate)
    {
        $this->certificates[] = (string) $certificate;
    }

    /**
     * @return string[]
     */
    public function getAllCertificates()
    {
        return $this->certificates;
    }

    public function setSignature(XMLSecurityDSig $signature)
    {
        $this->signature = $signature;
    }

    /**
     * @return XMLSecurityDSig
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * @return bool
     *
     * @throws LightSamlSecurityException|Exception
     */
    public function validate(XMLSecurityKey $key)
    {
        if (null == $this->signature) {
            return false;
        }

        // Must run before validateReference() because that call detaches sigNode from the document.
        $this->assertNoXmlSignatureWrapping();

        try {
            $this->signature->validateReference();
        } catch (Exception $e) {
            throw new LightSamlSecurityException('Digest validation failed', $e->getCode(), $e);
        }

        $key = $this->castKeyIfNecessary($key);

        if (false == $this->signature->verify($key)) {
            throw new LightSamlSecurityException('Unable to verify Signature');
        }

        return true;
    }

    /**
     * Rejects XML Signature Wrapping (XSW) attacks by verifying that each fragment Reference URI
     * points to the element that directly contains this ds:Signature, and that this ID is unique
     * in the document. Duplicate IDs are illegal in XML and are the prerequisite for XSW attacks
     * where an attacker buries a genuine signed element elsewhere in the document and presents a
     * forged element with a copied signature as a direct-child assertion.
     *
     * @throws LightSamlSecurityException
     */
    private function assertNoXmlSignatureWrapping(): void
    {
        $sigNode = $this->signature->sigNode;
        if (!$sigNode instanceof DOMElement) {
            return;
        }

        $doc = $sigNode->ownerDocument;
        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('ds', XMLSecurityDSig::XMLDSIGNS);

        $refs = $xpath->query('./ds:SignedInfo/ds:Reference', $sigNode);
        if (!$refs || $refs->length === 0) {
            return;
        }

        foreach ($refs as $ref) {
            /** @var DOMElement $ref */
            $uri = $ref->getAttribute('URI');
            if (!str_starts_with($uri, '#') || $uri === '#') {
                continue;
            }
            $id = substr($uri, 1);

            $parent = $sigNode->parentNode;
            if (!$parent instanceof DOMElement) {
                throw new LightSamlSecurityException('Enveloped signature has no parent element');
            }

            // Verify the parent element carries the referenced ID, using the same attribute names
            // that xmlseclibs uses (hardcoded 'Id' plus whatever idKeys are registered).
            $idAttrNames = array_merge(['Id'], $this->signature->idKeys);
            $parentId = null;
            foreach ($idAttrNames as $attrName) {
                $val = $parent->getAttribute($attrName);
                if ($val !== '') {
                    $parentId = $val;
                    break;
                }
            }

            if ($parentId !== $id) {
                throw new LightSamlSecurityException(sprintf(
                    'Signature Reference URI "#%s" does not match the enclosing element\'s ID "%s"',
                    $id,
                    (string) $parentId
                ));
            }

            // Build the same XPath predicate that xmlseclibs uses to resolve the reference, then
            // count matches. More than one element with the same ID means the document is malformed
            // and a wrapping attack is almost certainly in progress.
            $iDlist = '@Id="' . $id . '"';
            foreach ($this->signature->idKeys as $idKey) {
                $iDlist .= ' or @' . $idKey . '="' . $id . '"';
            }
            $matches = $xpath->query('//*[' . $iDlist . ']');
            if ($matches !== false && $matches->length > 1) {
                throw new LightSamlSecurityException(sprintf(
                    'Duplicate ID "%s" found in document: XML Signature Wrapping attack detected',
                    $id
                ));
            }
        }
    }

    /**
     * @return string
     *
     * @throws LightSamlXmlException
     */
    public function getAlgorithm()
    {
        $xpath = new DOMXPath(
            $this->signature->sigNode instanceof DOMDocument
            ? $this->signature->sigNode
            : $this->signature->sigNode->ownerDocument
        );
        $xpath->registerNamespace('ds', XMLSecurityDSig::XMLDSIGNS);

        $list = $xpath->query('./ds:SignedInfo/ds:SignatureMethod', $this->signature->sigNode);
        if (!$list || 0 == $list->length) {
            throw new LightSamlXmlException('Missing SignatureMethod element');
        }
        /** @var $sigMethod \DOMElement */
        $sigMethod = $list->item(0);
        if (!$sigMethod->hasAttribute('Algorithm')) {
            throw new LightSamlXmlException('Missing Algorithm-attribute on SignatureMethod element.');
        }

        return $sigMethod->getAttribute('Algorithm');
    }

    /**
     * @throws LogicException
     */
    public function serialize(DOMNode $parent, SerializationContext $context): never
    {
        throw new LogicException('SignatureXmlReader can not be serialized');
    }

    /**
     * @throws Exception
     */
    public function deserialize(DOMNode $node, DeserializationContext $context)
    {
        $this->checkXmlNodeName($node, 'Signature', SamlConstants::NS_XMLDSIG);

        $this->signature = new XMLSecurityDSig();
        $this->signature->idKeys[] = $this->getIDName();
        $this->signature->sigNode = $node;
        $this->signature->canonicalizeSignedInfo();

        $this->key = null;
        $key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, ['type' => 'public']);
        XMLSecEnc::staticLocateKeyInfo($key, $node);
        if ($key->name || $key->key) {
            $this->key = $key;
        }

        $this->certificates = [];
        $list = $context->getXpath()->query('./ds:KeyInfo/ds:X509Data/ds:X509Certificate', $node);
        foreach ($list as $certNode) {
            $certData = trim($certNode->textContent);
            $certData = str_replace(["\r", "\n", "\t", ' '], '', $certData);
            $this->certificates[] = $certData;
        }
    }
}
