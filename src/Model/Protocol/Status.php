<?php

namespace LightSaml\Model\Protocol;

use DOMNode;
use LightSaml\Model\AbstractSamlModel;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LightSaml\SamlConstants;

class Status extends AbstractSamlModel
{
    /**
     * @param string $statusMessage
     */
    public function __construct(protected ?StatusCode $statusCode = null, protected $statusMessage = null)
    {
    }

    /**
     * @return Status
     */
    public function setStatusCode(StatusCode $statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * @return StatusCode
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param string|null $message
     */
    public function setStatusMessage($message)
    {
        $this->statusMessage = (string) $message;
    }

    /**
     * @return string|null
     */
    public function getStatusMessage()
    {
        return $this->statusMessage;
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->getStatusCode() && SamlConstants::STATUS_SUCCESS == $this->getStatusCode()->getValue();
    }

    /**
     * @return Status
     */
    public function setSuccess()
    {
        $this->setStatusCode(new StatusCode());
        $this->getStatusCode()->setValue(SamlConstants::STATUS_SUCCESS);

        return $this;
    }

    /**
     * @return void
     */
    public function serialize(DOMNode $parent, SerializationContext $context)
    {
        $result = $this->createElement('samlp:Status', SamlConstants::NS_PROTOCOL, $parent, $context);

        $this->singleElementsToXml(['StatusCode', 'StatusMessage'], $result, $context, SamlConstants::NS_PROTOCOL);
    }

    public function deserialize(DOMNode $node, DeserializationContext $context)
    {
        $this->checkXmlNodeName($node, 'Status', SamlConstants::NS_PROTOCOL);

        $this->singleElementsFromXml($node, $context, [
            'StatusCode' => ['samlp', StatusCode::class],
            'StatusMessage' => ['samlp', null],
        ]);
    }
}
