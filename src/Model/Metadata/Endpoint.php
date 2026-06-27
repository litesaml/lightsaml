<?php

namespace LightSaml\Model\Metadata;

use DOMNode;
use LightSaml\Model\AbstractSamlModel;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;

abstract class Endpoint extends AbstractSamlModel
{
    protected ?string $responseLocation = null;

    public function __construct(protected ?string $location = null, protected ?string $binding = null)
    {
    }

    public function setBinding(string $binding): Endpoint
    {
        $this->binding = $binding;

        return $this;
    }

    public function getBinding(): ?string
    {
        return $this->binding;
    }

    public function setLocation(string $location): Endpoint
    {
        $this->location = $location;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setResponseLocation(?string $responseLocation): Endpoint
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
