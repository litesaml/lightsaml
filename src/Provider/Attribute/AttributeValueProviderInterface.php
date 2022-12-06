<?php

namespace LightSaml\Provider\Attribute;

use LightSaml\Context\Profile\AssertionContext;
use LightSaml\Model\Assertion\Attribute;

interface AttributeValueProviderInterface
{
    /**
     * @return Attribute[]
     */
    public function getValues(AssertionContext $context);
}
