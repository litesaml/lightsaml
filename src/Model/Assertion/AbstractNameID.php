<?php

namespace LightSaml\Model\Assertion;

use DOMNode;
use LightSaml\Error\LightSamlModelException;
use LightSaml\Model\AbstractSamlModel;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LightSaml\SamlConstants;

abstract class AbstractNameID extends AbstractSamlModel
{
    /**
     * @var string|null
     */
    protected $nameQualifier;

    /**
     * @var string|null
     */
    protected $spNameQualifier;

    /**
     * @var string|null
     */
    protected $spProvidedId;

    /**
     * @param string $value
     * @param string $format
     */
    public function __construct(protected $value = null, protected $format = null)
    {
    }

    
    public function setFormat(?string $format): \LightSaml\Model\Assertion\AbstractNameID
    {
        $this->format = (string) $format;

        return $this;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    
    public function setNameQualifier(?string $nameQualifier): \LightSaml\Model\Assertion\AbstractNameID
    {
        $this->nameQualifier = (string) $nameQualifier;

        return $this;
    }

    public function getNameQualifier(): ?string
    {
        return $this->nameQualifier;
    }

    
    public function setSPNameQualifier(?string $spNameQualifier): \LightSaml\Model\Assertion\AbstractNameID
    {
        $this->spNameQualifier = (string) $spNameQualifier;

        return $this;
    }

    public function getSPNameQualifier(): ?string
    {
        return $this->spNameQualifier;
    }

    
    public function setSPProvidedID(?string $spProvidedId): \LightSaml\Model\Assertion\AbstractNameID
    {
        $this->spProvidedId = (string) $spProvidedId;

        return $this;
    }

    public function getSPProvidedID(): ?string
    {
        return $this->spProvidedId;
    }

    
    public function setValue(string $value): \LightSaml\Model\Assertion\AbstractNameID
    {
        $this->value = $value;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    protected function prepareForXml()
    {
        if (false == $this->getValue()) {
            throw new LightSamlModelException('NameID value not set');
        }
    }

    public function serialize(DOMNode $parent, SerializationContext $context): void
    {
        $this->prepareForXml();
        if (SamlConstants::NS_ASSERTION == $parent->namespaceURI) {
            $result = $this->createElement($this->getElementName(), SamlConstants::NS_ASSERTION, $parent, $context);
        } else {
            $result = $this->createElement('saml:' . $this->getElementName(), SamlConstants::NS_ASSERTION, $parent, $context);
        }

        /* @var \DOMElement $parent */
        $this->attributesToXml(['Format', 'NameQualifier', 'SPNameQualifier', 'SPProvidedID'], $result);
        $result->nodeValue = $this->getValue();
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, $this->getElementName(), SamlConstants::NS_ASSERTION);

        $this->attributesFromXml($node, ['NameQualifier', 'Format', 'SPNameQualifier', 'SPProvidedID']);
        $this->setValue($node->textContent);
    }

    abstract protected function getElementName(): string;
}
