<?php

namespace LightSaml\Model\Protocol;

use DOMNode;
use LightSaml\Context\Model\DeserializationContext;
use LightSaml\Context\Model\SerializationContext;
use LightSaml\Model\AbstractSamlModel;
use LightSaml\SamlConstants;

class Status extends AbstractSamlModel
{
    /**
     * @param string $statusMessage
     */
    public function __construct(protected ?StatusCode $statusCode = null, protected $statusMessage = null)
    {
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

    public function setStatusMessage(?string $message): void
    {
        $this->statusMessage = (string) $message;
    }

    public function getStatusMessage(): ?string
    {
        return $this->statusMessage;
    }

    public function isSuccess(): bool
    {
        return $this->getStatusCode() && SamlConstants::STATUS_SUCCESS === $this->getStatusCode()->getValue();
    }

    public function setSuccess(): static
    {
        $this->setStatusCode(new StatusCode());
        $this->getStatusCode()->setValue(SamlConstants::STATUS_SUCCESS);

        return $this;
    }

    public function serialize(DOMNode $parent, SerializationContext $context): void
    {
        $result = $this->createElement('samlp:Status', SamlConstants::NS_PROTOCOL, $parent, $context);

        $this->singleElementsToXml(['StatusCode', 'StatusMessage'], $result, $context, SamlConstants::NS_PROTOCOL);
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'Status', SamlConstants::NS_PROTOCOL);

        $this->singleElementsFromXml($node, $context, [
            'StatusCode' => ['samlp', StatusCode::class],
            'StatusMessage' => ['samlp', null],
        ]);
    }
}
