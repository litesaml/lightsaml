<?php

namespace Tests;

use LightSaml\SamlConstants;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;

class SamlConstantsTest extends BaseTestCase
{
    #[DataProvider('methodsProvider')]
    public function test__is_not_valid($method)
    {
        $this->assertFalse(SamlConstants::$method('Nonsense'));
    }

    #[DataProvider('constantsProvider')]
    public function test__is_valid_method($method, $constant)
    {
        $value = constant('\LightSaml\SamlConstants::' . $constant);
        $this->assertTrue(SamlConstants::$method($value));
    }

    public static function methodsProvider()
    {
        return [['isProtocolValid'], ['isNsValid'], ['isNameIdFormatValid'], ['isBindingValid'], ['isStatusValid'], ['isConfirmationMethodValid'], ['isAuthnContextValid'], ['isLogoutReasonValid']];
    }

    public function constantsProvider(): array
    {
        return array_merge(
            $this->getConstants('Protocol'),
            $this->getConstants('Ns'),
            $this->getConstants('NameIdFormat'),
            $this->getConstants('Binding'),
            $this->getConstants('Status'),
            $this->getConstants('ConfirmationMethod'),
            $this->getConstants('AuthnContext'),
            $this->getConstants('LogoutReason')
        );
    }

    public function getConstants($method)
    {
        $ret = [];
        $ref = new ReflectionClass(SamlConstants::class);
        $prefix = strtoupper(
            preg_replace('/([a-z])([A-Z])/', '$1_$2', $method)
        );
        $method = 'is' . $method . 'Valid';

        foreach (array_keys($ref->getConstants()) as $constant) {
            if (str_starts_with($constant, $prefix)) {
                $ret[] = [$method, $constant];
            }
        }

        return $ret;
    }
}
