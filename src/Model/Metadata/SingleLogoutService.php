<?php

namespace LightSaml\Model\Metadata;

use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LightSaml\SamlConstants;

class SingleLogoutService extends Endpoint
{
    public function serialize(\DOMNode $parent, SerializationContext $context)
    {
        $result = $this->createElement('SingleLogoutService', SamlConstants::NS_METADATA, $parent, $context);
        parent::serialize($result, $context);
    }

    public function deserialize(\DOMNode $node, DeserializationContext $context)
    {
        $this->checkXmlNodeName($node, 'SingleLogoutService', SamlConstants::NS_METADATA);

        parent::deserialize($node, $context);
    }
}
