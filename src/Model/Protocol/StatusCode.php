<?php

namespace LightSaml\Model\Protocol;

use DOMNode;
use LightSaml\Model\AbstractSamlModel;
use LightSaml\Context\Model\DeserializationContext;
use LightSaml\Context\Model\SerializationContext;
use LightSaml\SamlConstants;

class StatusCode extends AbstractSamlModel
{
    protected ?StatusCode $statusCode = null;

    public function __construct(protected ?string $value = null)
    {
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setStatusCode(StatusCode $statusCode): static
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function getStatusCode(): ?StatusCode
    {
        return $this->statusCode;
    }

    public function serialize(DOMNode $parent, SerializationContext $context): void
    {
        $result = $this->createElement('samlp:StatusCode', SamlConstants::NS_PROTOCOL, $parent, $context);

        $this->attributesToXml(['Value'], $result);

        $this->singleElementsToXml(['StatusCode'], $result, $context);
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'StatusCode', SamlConstants::NS_PROTOCOL);

        $this->attributesFromXml($node, ['Value']);

        $this->singleElementsFromXml($node, $context, [
            'StatusCode' => ['samlp', StatusCode::class],
        ]);
    }
}
