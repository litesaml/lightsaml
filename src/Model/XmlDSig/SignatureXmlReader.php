<?php

namespace LightSaml\Model\XmlDSig;

use DOMElement;
use DOMNode;
use DOMXPath;
use Exception;
use LightSaml\Context\Model\DeserializationContext;
use LightSaml\Context\Model\SerializationContext;
use LightSaml\Error\LightSamlSecurityException;
use LightSaml\Error\LightSamlXmlException;
use LightSaml\SamlConstants;
use LogicException;
use RobRichards\XMLSecLibs\XMLSecEnc;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class SignatureXmlReader extends AbstractSignatureReader
{
    protected ?XMLSecurityDSig $signature = null;

    /** @var string[] */
    protected array $certificates = [];

    public function addCertificate(string $certificate): void
    {
        $this->certificates[] = $certificate;
    }

    /**
     * @return string[]
     */
    public function getAllCertificates(): array
    {
        return $this->certificates;
    }

    public function setSignature(XMLSecurityDSig $signature): void
    {
        $this->signature = $signature;
    }

    public function getSignature(): XMLSecurityDSig
    {
        return $this->signature;
    }

    /**
     * @throws LightSamlSecurityException|Exception
     */
    public function validate(XMLSecurityKey $key): bool
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
     * @throws LightSamlXmlException
     */
    public function getAlgorithm(): string
    {
        $sigNode = $this->signature->sigNode;
        $xpath = new DOMXPath($sigNode->ownerDocument);
        $xpath->registerNamespace('ds', XMLSecurityDSig::XMLDSIGNS);

        $list = $xpath->query('./ds:SignedInfo/ds:SignatureMethod', $sigNode);
        if (!$list || 0 == $list->length) {
            throw new LightSamlXmlException('Missing SignatureMethod element');
        }
        $sigMethod = $list->item(0);
        if (!$sigMethod instanceof DOMElement || !$sigMethod->hasAttribute('Algorithm')) {
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
    public function deserialize(DOMNode $node, DeserializationContext $context): void
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
