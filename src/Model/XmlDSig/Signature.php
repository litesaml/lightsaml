<?php

namespace LightSaml\Model\XmlDSig;

use DOMNode;
use LightSaml\Model\AbstractSamlModel;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;

class Signature extends AbstractSamlModel
{
    /**
     * @return string
     */
    protected function getIDName()
    {
        return 'ID';
    }

    /**
     * @return void
     */
    public function serialize(DOMNode $parent, SerializationContext $context)
    {
        //
    }

    /**
     * @return void
     */
    public function deserialize(DOMNode $node, DeserializationContext $context)
    {
        //
    }
}
