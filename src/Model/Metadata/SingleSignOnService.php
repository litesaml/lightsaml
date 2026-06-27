<?php

namespace LightSaml\Model\Metadata;

use DOMNode;
use LightSaml\Context\Model\DeserializationContext;
use LightSaml\Context\Model\SerializationContext;
use LightSaml\SamlConstants;

class SingleSignOnService extends Endpoint
{
    public function serialize(DOMNode $parent, SerializationContext $context): void
    {
        $result = $this->createElement('SingleSignOnService', SamlConstants::NS_METADATA, $parent, $context);

        parent::serialize($result, $context);
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'SingleSignOnService', SamlConstants::NS_METADATA);

        parent::deserialize($node, $context);
    }
}
