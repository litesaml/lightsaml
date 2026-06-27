<?php

namespace LightSaml\Model;

use DOMNode;
use LightSaml\Context\Model\DeserializationContext;
use LightSaml\Context\Model\SerializationContext;

interface SamlElementInterface
{
    public function serialize(DOMNode $parent, SerializationContext $context): void;

    public function deserialize(DOMNode $node, DeserializationContext $context): void;
}
