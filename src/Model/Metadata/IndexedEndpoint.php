<?php

namespace LightSaml\Model\Metadata;

use DOMNode;
use LightSaml\Context\Model\DeserializationContext;
use LightSaml\Context\Model\SerializationContext;

class IndexedEndpoint extends Endpoint
{
    protected ?int $index = null;

    protected ?bool $isDefault = null;

    public function setIsDefault(mixed $isDefault): static
    {
        $this->isDefault = filter_var($isDefault, FILTER_VALIDATE_BOOLEAN, ['flags' => FILTER_NULL_ON_FAILURE]);

        return $this;
    }

    public function getIsDefaultString(): string
    {
        return $this->isDefault ? 'true' : 'false';
    }

    public function getIsDefaultBool(): ?bool
    {
        return $this->isDefault;
    }

    public function setIndex(mixed $index): static
    {
        $this->index = (int) $index;

        return $this;
    }

    public function getIndex(): ?int
    {
        return $this->index;
    }

    public function serialize(DOMNode $parent, SerializationContext $context): void
    {
        $this->attributesToXml(['index', 'isDefault'], $parent);
        parent::serialize($parent, $context);
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): void
    {
        $this->attributesFromXml($node, ['index', 'isDefault']);

        parent::deserialize($node, $context);
    }
}
