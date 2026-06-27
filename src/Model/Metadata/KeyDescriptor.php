<?php

namespace LightSaml\Model\Metadata;

use DOMElement;
use DOMNode;
use InvalidArgumentException;
use LightSaml\Context\Model\DeserializationContext;
use LightSaml\Context\Model\SerializationContext;
use LightSaml\Credential\X509Certificate;
use LightSaml\Error\LightSamlXmlException;
use LightSaml\Model\AbstractSamlModel;
use LightSaml\SamlConstants;

class KeyDescriptor extends AbstractSamlModel
{
    public const USE_SIGNING = 'signing';
    public const USE_ENCRYPTION = 'encryption';

    public function __construct(protected ?string $use = null, private ?X509Certificate $certificate = null)
    {
    }

    /**
     *
     * @throws InvalidArgumentException
     */
    public function setUse(string $use): static
    {
        $use = trim($use);
        if (false != $use && self::USE_ENCRYPTION !== $use && self::USE_SIGNING !== $use) {
            throw new InvalidArgumentException(sprintf("Invalid use value '%s'", $use));
        }
        $this->use = $use;

        return $this;
    }

    public function getUse(): ?string
    {
        return $this->use;
    }

    public function setCertificate(X509Certificate $certificate): static
    {
        $this->certificate = $certificate;

        return $this;
    }

    /**
     */
    public function getCertificate(): ?X509Certificate
    {
        return $this->certificate;
    }

    public function serialize(DOMNode $parent, SerializationContext $context): void
    {
        $result = $this->createElement('KeyDescriptor', SamlConstants::NS_METADATA, $parent, $context);

        $this->attributesToXml(['use'], $result);

        $keyInfo = $this->createElement('ds:KeyInfo', SamlConstants::NS_XMLDSIG, $result, $context);
        $xData = $this->createElement('ds:X509Data', SamlConstants::NS_XMLDSIG, $keyInfo, $context);
        $xCert = $this->createElement('ds:X509Certificate', SamlConstants::NS_XMLDSIG, $xData, $context);

        $xCert->nodeValue = $this->getCertificate()->getData();
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'KeyDescriptor', SamlConstants::NS_METADATA);

        $this->attributesFromXml($node, ['use']);

        $list = $context->getXpath()->query('./ds:KeyInfo/ds:X509Data/ds:X509Certificate', $node);
        if (1 != $list->length) {
            throw new LightSamlXmlException('Missing X509Certificate node');
        }

        /** @var DOMElement $x509CertificateNode */
        $x509CertificateNode = $list->item(0);
        $certificateData = trim($x509CertificateNode->textContent);
        if (false == $certificateData) {
            throw new LightSamlXmlException('Missing certificate data');
        }

        $this->certificate = new X509Certificate();
        $this->certificate->setData($certificateData);
    }
}
