<?php

namespace LightSaml\Model;

use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;

interface SamlElementInterface
{
    /**
     * @return void
     */
    public function serialize(\DOMNode $parent, SerializationContext $context);

    /**
     * @return void
     */
    public function deserialize(\DOMNode $node, DeserializationContext $context);
}
