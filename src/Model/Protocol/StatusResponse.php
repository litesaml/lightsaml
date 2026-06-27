<?php

namespace LightSaml\Model\Protocol;

use DOMNode;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;

abstract class StatusResponse extends SamlMessage
{
    protected ?string $inResponseTo = null;

    protected ?Status $status = null;

    
    public function setInResponseTo(string $inResponseTo): \LightSaml\Model\Protocol\StatusResponse
    {
        $this->inResponseTo = $inResponseTo;

        return $this;
    }

    public function getInResponseTo(): ?string
    {
        return $this->inResponseTo;
    }

    public function setStatus(Status $status): \LightSaml\Model\Protocol\StatusResponse
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus(): ?\LightSaml\Model\Protocol\Status
    {
        return $this->status;
    }

    public function serialize(DOMNode $parent, SerializationContext $context): void
    {
        parent::serialize($parent, $context);

        $this->attributesToXml(['InResponseTo'], $parent);

        $this->singleElementsToXml(['Status'], $parent, $context);
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): void
    {
        $this->attributesFromXml($node, ['InResponseTo']);

        $this->singleElementsFromXml($node, $context, [
            'Status' => ['samlp', Status::class],
        ]);

        parent::deserialize($node, $context);
    }
}
