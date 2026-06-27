<?php

namespace Tests\Model\Assertion;

use LightSaml\Model\Assertion\Attribute;
use LightSaml\Model\Assertion\AttributeStatement;
use Tests\BaseTestCase;

class AttributeStatementTest extends BaseTestCase
{
    public function test_get_first_attribute_by_name_returns_attribute(): void
    {
        $attributeStatement = new AttributeStatement();
        $attributeStatement->addAttribute(new Attribute('a'));
        $attributeStatement->addAttribute($expected = new Attribute('b'));
        $attributeStatement->addAttribute(new Attribute('b'));
        $attributeStatement->addAttribute(new Attribute('c'));

        $this->assertSame($expected, $attributeStatement->getFirstAttributeByName('b'));
    }

    public function test_get_first_attribute_by_name_returns_null(): void
    {
        $attributeStatement = new AttributeStatement();
        $attributeStatement->addAttribute(new Attribute('a'));
        $attributeStatement->addAttribute(new Attribute('b'));
        $attributeStatement->addAttribute(new Attribute('c'));

        $this->assertNull($attributeStatement->getFirstAttributeByName('x'));
    }
}
