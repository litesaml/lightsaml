<?php

namespace LightSaml\Model\Protocol;

use DOMNode;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;

abstract class StatusResponse extends SamlMessage
{
    /** @var string */
    protected $inResponseTo;

    /** @var Status */
    protected $status;

    /**
     * @param string $inResponseTo
     *
     * @return StatusResponse
     */
    public function setInResponseTo($inResponseTo)
    {
        $this->inResponseTo = $inResponseTo;

        return $this;
    }

    /**
     * @return string
     */
    public function getInResponseTo()
    {
        return $this->inResponseTo;
    }

    /**
     * @return StatusResponse
     */
    public function setStatus(Status $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return void
     */
    public function serialize(DOMNode $parent, SerializationContext $context)
    {
        parent::serialize($parent, $context);

        $this->attributesToXml(['InResponseTo'], $parent);

        $this->singleElementsToXml(['Status'], $parent, $context);
    }

    public function deserialize(DOMNode $node, DeserializationContext $context)
    {
        $this->attributesFromXml($node, ['InResponseTo']);

        $this->singleElementsFromXml($node, $context, [
            'Status' => ['samlp', Status::class],
        ]);

        parent::deserialize($node, $context);
    }
}
