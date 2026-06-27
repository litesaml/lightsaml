<?php

namespace LightSaml\Model\Assertion;

use DOMNode;
use LightSaml\Context\Model\DeserializationContext;
use LightSaml\Context\Model\SerializationContext;
use LightSaml\Error\LightSamlModelException;
use LightSaml\Model\AbstractSamlModel;
use LightSaml\SamlConstants;

abstract class AbstractNameID extends AbstractSamlModel
{
    protected ?string $nameQualifier = null;

    protected ?string $spNameQualifier = null;

    protected ?string $spProvidedId = null;

    public function __construct(protected ?string $value = null, protected ?string $format = null)
    {
    }

    public function setFormat(?string $format): static
    {
        $this->format = (string) $format;

        return $this;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setNameQualifier(?string $nameQualifier): static
    {
        $this->nameQualifier = (string) $nameQualifier;

        return $this;
    }

    public function getNameQualifier(): ?string
    {
        return $this->nameQualifier;
    }

    public function setSPNameQualifier(?string $spNameQualifier): static
    {
        $this->spNameQualifier = (string) $spNameQualifier;

        return $this;
    }

    public function getSPNameQualifier(): ?string
    {
        return $this->spNameQualifier;
    }

    public function setSPProvidedID(?string $spProvidedId): static
    {
        $this->spProvidedId = (string) $spProvidedId;

        return $this;
    }

    public function getSPProvidedID(): ?string
    {
        return $this->spProvidedId;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    protected function prepareForXml(): void
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
