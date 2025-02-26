<?php

namespace Tests\Model\Xsd;

use LightSaml\Validator\Model\Xsd\XsdValidator;
use Tests\BaseTestCase;

class XsdValidatorTest extends BaseTestCase
{
    public function test_fails_on_invalid_xml()
    {
        $validator = new XsdValidator();
        $arr = $validator->validateProtocol('<a><');
        $this->assertGreaterThan(0, count($arr));
    }

    public function test_fails_on_empty_xml()
    {
        $validator = new XsdValidator();
        $arr = $validator->validateProtocol('');
        $this->assertGreaterThan(0, count($arr));
    }
}
