<?php

namespace LightSaml\Model\Metadata;

use DOMNode;
use InvalidArgumentException;
use LightSaml\Credential\X509Certificate;
use LightSaml\Error\LightSamlXmlException;
use LightSaml\Model\AbstractSamlModel;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LightSaml\SamlConstants;

class KeyDescriptor extends AbstractSamlModel
{
    public const USE_SIGNING = 'signing';
    public const USE_ENCRYPTION = 'encryption';

    /**
     * @param string|null $use
     */
    public function __construct(protected $use = null, private ?X509Certificate $certificate = null)
    {
    }

    /**
     * @param string $use
     *
     * @return KeyDescriptor
     *
     * @throws InvalidArgumentException
     */
    public function setUse($use)
    {
        $use = trim($use);
        if (false != $use && self::USE_ENCRYPTION !== $use && self::USE_SIGNING !== $use) {
            throw new InvalidArgumentException(sprintf("Invalid use value '%s'", $use));
        }
        $this->use = $use;

        return $this;
    }

    /**
     * @return string
     */
    public function getUse()
    {
        return $this->use;
    }

    /**
     * @return KeyDescriptor
     */
    public function setCertificate(X509Certificate $certificate)
    {
        $this->certificate = $certificate;

        return $this;
    }

    /**
     * @return X509Certificate
     */
    public function getCertificate()
    {
        return $this->certificate;
    }

    /**
     * @return void
     */
    public function serialize(DOMNode $parent, SerializationContext $context)
    {
        $result = $this->createElement('KeyDescriptor', SamlConstants::NS_METADATA, $parent, $context);

        $this->attributesToXml(['use'], $result);

        $keyInfo = $this->createElement('ds:KeyInfo', SamlConstants::NS_XMLDSIG, $result, $context);
        $xData = $this->createElement('ds:X509Data', SamlConstants::NS_XMLDSIG, $keyInfo, $context);
        $xCert = $this->createElement('ds:X509Certificate', SamlConstants::NS_XMLDSIG, $xData, $context);

        $xCert->nodeValue = $this->getCertificate()->getData();
    }

    public function deserialize(DOMNode $node, DeserializationContext $context)
    {
        $this->checkXmlNodeName($node, 'KeyDescriptor', SamlConstants::NS_METADATA);

        $this->attributesFromXml($node, ['use']);

        $list = $context->getXpath()->query('./ds:KeyInfo/ds:X509Data/ds:X509Certificate', $node);
        if (1 != $list->length) {
            throw new LightSamlXmlException('Missing X509Certificate node');
        }

        /** @var $x509CertificateNode \DOMElement */
        $x509CertificateNode = $list->item(0);
        $certificateData = trim($x509CertificateNode->textContent);
        if (false == $certificateData) {
            throw new LightSamlXmlException('Missing certificate data');
        }

        $this->certificate = new X509Certificate();
        $this->certificate->setData($certificateData);
    }
}
