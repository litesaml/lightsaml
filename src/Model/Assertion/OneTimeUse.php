<?php

namespace LightSaml\Model\Assertion;

use DOMNode;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LightSaml\SamlConstants;

class OneTimeUse extends AbstractCondition
{
    public function serialize(DOMNode $parent, SerializationContext $context): void
    {
        $this->createElement('OneTimeUse', SamlConstants::NS_ASSERTION, $parent, $context);
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'OneTimeUse', SamlConstants::NS_ASSERTION);
    }
}
