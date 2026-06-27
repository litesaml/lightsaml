<?php

namespace LightSaml\Model\Metadata;

use DOMNode;
use LightSaml\Context\Model\DeserializationContext;
use LightSaml\Context\Model\SerializationContext;
use LightSaml\SamlConstants;

class AssertionConsumerService extends IndexedEndpoint
{
    public function serialize(DOMNode $parent, SerializationContext $context): void
    {
        $result = $this->createElement('AssertionConsumerService', SamlConstants::NS_METADATA, $parent, $context);
        parent::serialize($result, $context);
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'AssertionConsumerService', SamlConstants::NS_METADATA);
        parent::deserialize($node, $context);
    }
}
