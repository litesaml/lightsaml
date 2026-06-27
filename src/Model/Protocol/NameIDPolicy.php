<?php

namespace LightSaml\Model\Protocol;

use DOMNode;
use LightSaml\Model\AbstractSamlModel;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LightSaml\SamlConstants;

class NameIDPolicy extends AbstractSamlModel
{
    protected ?string $spNameQualifier = null;

    public function __construct(protected ?string $format = null, protected ?bool $allowCreate = null)
    {
    }

    
    public function setAllowCreate(string|bool|null $allowCreate): static
    {
        if (null === $allowCreate) {
            $this->allowCreate = null;
        } elseif (is_string($allowCreate) || is_int($allowCreate)) {
            $this->allowCreate = 0 == strcasecmp($allowCreate, 'true') || true === $allowCreate || 1 == $allowCreate;
        } else {
            $this->allowCreate = $allowCreate;
        }

        return $this;
    }

    public function getAllowCreate(): ?bool
    {
        return $this->allowCreate;
    }

    public function getAllowCreateString(): ?string
    {
        if (null === $this->allowCreate) {
            return null;
        }

        return $this->allowCreate ? 'true' : 'false';
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

    
    public function setSPNameQualifier(?string $spNameQualifier): static
    {
        $this->spNameQualifier = $spNameQualifier;

        return $this;
    }

    public function getSPNameQualifier(): ?string
    {
        return $this->spNameQualifier;
    }

    public function serialize(DOMNode $parent, SerializationContext $context): void
    {
        $result = $this->createElement('NameIDPolicy', SamlConstants::NS_PROTOCOL, $parent, $context);

        $this->attributesToXml(['Format', 'SPNameQualifier', 'AllowCreate'], $result);
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'NameIDPolicy', SamlConstants::NS_PROTOCOL);

        $this->attributesFromXml($node, ['Format', 'SPNameQualifier', 'AllowCreate']);
    }
}
