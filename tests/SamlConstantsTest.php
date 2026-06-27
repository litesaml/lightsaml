<?php

namespace Tests;

use LightSaml\SamlConstants;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;

class SamlConstantsTest extends BaseTestCase
{
    #[DataProvider('methodsProvider')]
    public function test__is_not_valid(string $method): void
    {
        $this->assertFalse(SamlConstants::$method('Nonsense'));
    }

    #[DataProvider('constantsProvider')]
    public function test__is_valid_method($method, string $constant): void
    {
        $value = constant('\LightSaml\SamlConstants::' . $constant);
        $this->assertTrue(SamlConstants::$method($value));
    }

    public static function methodsProvider(): array
    {
        return [['isProtocolValid'], ['isNsValid'], ['isNameIdFormatValid'], ['isBindingValid'], ['isStatusValid'], ['isConfirmationMethodValid'], ['isAuthnContextValid'], ['isLogoutReasonValid']];
    }

    public static function constantsProvider(): array
    {
        return array_merge(
            self::getConstants('Protocol'),
            self::getConstants('Ns'),
            self::getConstants('NameIdFormat'),
            self::getConstants('Binding'),
            self::getConstants('Status'),
            self::getConstants('ConfirmationMethod'),
            self::getConstants('AuthnContext'),
            self::getConstants('LogoutReason')
        );
    }

    /**
     * @return int[][]|string[][]
     */
    public static function getConstants($method): array
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
