<?php

namespace LightSaml\Model\XmlDSig;

use DOMDocument;
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
