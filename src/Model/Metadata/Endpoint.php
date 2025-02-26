<?php

namespace LightSaml\Model\Metadata;

use DOMNode;
use LightSaml\Model\AbstractSamlModel;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;

abstract class Endpoint extends AbstractSamlModel
{
    /** @var string|null */
    protected $responseLocation;

    /**
     * @param string $location
     * @param string $binding
     */
    public function __construct(protected $location = null, protected $binding = null)
    {
    }

    /**
     * @param string $binding
     *
     * @return Endpoint
     */
    public function setBinding($binding)
    {
        $this->binding = (string) $binding;

        return $this;
    }

    /**
     * @return string
     */
    public function getBinding()
    {
        return $this->binding;
    }

    /**
     * @param string $location
     *
     * @return Endpoint
     */
    public function setLocation($location)
    {
        $this->location = (string) $location;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param string|null $responseLocation
     *
     * @return Endpoint
     */
    public function setResponseLocation($responseLocation)
    {
        $this->responseLocation = $responseLocation ? (string) $responseLocation : null;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getResponseLocation()
    {
        return $this->responseLocation;
    }

    public function serialize(DOMNode $parent, SerializationContext $context)
    {
        $this->attributesToXml(['Binding', 'Location', 'ResponseLocation'], $parent);
    }

    public function deserialize(DOMNode $node, DeserializationContext $context)
    {
        $this->attributesFromXml($node, ['Binding', 'Location', 'ResponseLocation']);
    }
}
