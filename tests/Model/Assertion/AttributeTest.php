<?php

namespace Tests\Model\Assertion;

use LightSaml\Model\Assertion\Attribute;
use Tests\BaseTestCase;

class AttributeTest extends BaseTestCase
{
    public function test_set_scalar_value(): void
    {
        $attribute = new Attribute();
        $attribute->setAttributeValue($value = '123');

        $this->assertEquals([$value], $attribute->getAllAttributeValues());
    }

    public function test_set_array_value(): void
    {
        $attribute = new Attribute();
        $attribute->setAttributeValue($values = ['111', '222']);

        $this->assertEquals($values, $attribute->getAllAttributeValues());
    }

    public function test_set_get_name_format(): void
    {
        $attribute = new Attribute();
        $attribute->setNameFormat($expected = 'format');
        $this->assertEquals($expected, $attribute->getNameFormat());
    }
}
