<?php

namespace LightSaml\Model\Metadata;

use DOMNode;
use LightSaml\Context\Model\DeserializationContext;
use LightSaml\Context\Model\SerializationContext;
use LightSaml\Model\AbstractSamlModel;

abstract class Endpoint extends AbstractSamlModel
{
    protected ?string $responseLocation = null;

    public function __construct(protected ?string $location = null, protected ?string $binding = null)
    {
    }

    public function setBinding(string $binding): static
    {
        $this->binding = $binding;

        return $this;
    }

    public function getBinding(): ?string
    {
        return $this->binding;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setResponseLocation(?string $responseLocation): static
    {
        $this->responseLocation = $responseLocation ?: null;

        return $this;
    }

    public function getResponseLocation(): ?string
    {
        return $this->responseLocation;
    }

    public function serialize(DOMNode $parent, SerializationContext $context): void
    {
        $this->attributesToXml(['Binding', 'Location', 'ResponseLocation'], $parent);
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): void
    {
        $this->attributesFromXml($node, ['Binding', 'Location', 'ResponseLocation']);
    }
}
