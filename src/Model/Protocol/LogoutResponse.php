<?php

namespace LightSaml\Model\Protocol;

use DOMNode;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LightSaml\SamlConstants;

class LogoutResponse extends StatusResponse
{
    public function serialize(DOMNode $parent, SerializationContext $context)
    {
        $result = $this->createElement('samlp:LogoutResponse', SamlConstants::NS_PROTOCOL, $parent, $context);

        parent::serialize($result, $context);

        // must be done here at the end and not in a base class where declared in order to include signing of the elements added here
        $this->singleElementsToXml(['Signature'], $result, $context);
    }

    public function deserialize(DOMNode $node, DeserializationContext $context)
    {
        $this->checkXmlNodeName($node, 'LogoutResponse', SamlConstants::NS_PROTOCOL);

        parent::deserialize($node, $context);
    }
}
