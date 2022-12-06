<?php

namespace LightSaml\Model\Assertion;

use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LightSaml\SamlConstants;

class OneTimeUse extends AbstractCondition
{
    /**
     * @return void
     */
    public function serialize(\DOMNode $parent, SerializationContext $context)
    {
        $this->createElement('OneTimeUse', SamlConstants::NS_ASSERTION, $parent, $context);
    }

    public function deserialize(\DOMNode $node, DeserializationContext $context)
    {
        $this->checkXmlNodeName($node, 'OneTimeUse', SamlConstants::NS_ASSERTION);
    }
}
