<?php

namespace LightSaml\Model;

use DOMNode;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;

interface SamlElementInterface
{
    public function serialize(DOMNode $parent, SerializationContext $context): void;

    public function deserialize(DOMNode $node, DeserializationContext $context): void;
}
