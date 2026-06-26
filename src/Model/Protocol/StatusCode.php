<?php

namespace LightSaml\Model\Protocol;

use DOMNode;
use LightSaml\Model\AbstractSamlModel;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LightSaml\SamlConstants;

class StatusCode extends AbstractSamlModel
{
    /** @var StatusCode|null */
    protected $statusCode;

    /**
     * @param string $value
     */
    public function __construct(protected $value = null)
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

    /**
     * @param StatusCode|null $statusCode
     */
    public function setStatusCode(StatusCode $statusCode): static
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function getStatusCode(): ?\LightSaml\Model\Protocol\StatusCode
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
