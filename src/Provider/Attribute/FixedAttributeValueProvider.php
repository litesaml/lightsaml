<?php

namespace LightSaml\Provider\Attribute;

use LightSaml\Context\Profile\AssertionContext;
use LightSaml\Model\Assertion\Attribute;

class FixedAttributeValueProvider implements AttributeValueProviderInterface
{
    /** @var Attribute[] */
    protected $attributes = [];

    /**
     * @return FixedAttributeValueProvider
     */
    public function add(Attribute $attribute)
    {
        $this->attributes[] = $attribute;

        return $this;
    }

    /**
     * @param Attribute[] $attributes
     *
     * @return FixedAttributeValueProvider
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = [];
        foreach ($attributes as $attribute) {
            $this->add($attribute);
        }

        return $this;
    }

    /**
     * @return Attribute[]
     */
    public function getValues(AssertionContext $context)
    {
        return $this->attributes;
    }
}
