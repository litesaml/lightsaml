<?php

namespace Tests;

use DateTime;
use InvalidArgumentException;
use LightSaml\Helper;
use LightSaml\SamlConstants;
use PHPUnit\Framework\Attributes\DataProvider;

class HelperTest extends BaseTestCase
{
    /** @var array<array{int, string}> */
    protected static array $timestamps = [[1412399250, '2014-10-04T05:07:30Z'], [1412368132, '2014-10-03T20:28:52Z'], [1412331547, '2014-10-03T10:19:07Z']];

    /** @return array<array{int, string}> */
    public static function timestamp2StringProvider(): array
    {
        return self::$timestamps;
    }

    /** @return array<array{string, int}> */
    public static function string2TimestampProvider(): array
    {
        $timestamps = array_merge(
            self::$timestamps,
            [
                [1412399250, '2014-10-04T05:07:30+00:00'],
                [1412368132, '2014-10-03T20:28:52+00:00'],
                [1412331547, '2014-10-03T10:19:07+00:00'],
                [1412399250, '2014-10-04T05:07:30.000+00:00'],
                [1412368132, '2014-10-03T20:28:52.000+00:00'],
                [1412331547, '2014-10-03T10:19:07.000+00:00'],
                [1412399250, '2014-10-04T06:07:30+01:00'],
                [1412368132, '2014-10-03T21:28:52+01:00'],
                [1412331547, '2014-10-03T11:19:07+01:00'],
                [1412399250, '2014-10-04T06:07:30.000+01:00'],
                [1412368132, '2014-10-03T21:28:52.000+01:00'],
                [1412331547, '2014-10-03T11:19:07.000+01:00'],
            ]
        );
        $result = [];
        foreach ($timestamps as $arr) {
            $result[] = [$arr[1], $arr[0]];
        }

        return $result;
    }

    #[DataProvider('timestamp2StringProvider')]
    public function test__time_to_string(int $timestamp, string $string): void
    {
        $this->assertEquals($string, Helper::time2string($timestamp));
    }

    /**
     * @param string $value
     */
    #[DataProvider('string2TimestampProvider')]
    public function test__get_timestamp_from_value_with_string(int|string|DateTime $value, int $timestamp): void
    {
        $this->assertEquals($timestamp, Helper::getTimestampFromValue($value));
    }

    #[DataProvider('string2TimestampProvider')]
    public function test__get_timestamp_from_value_with_date_time(string $value, int $timestamp): void
    {
        $dt = new DateTime('@' . $timestamp);
        $this->assertEquals($timestamp, Helper::getTimestampFromValue($dt));
    }

    #[DataProvider('string2TimestampProvider')]
    public function test__get_timestamp_from_value_with_int(string $value, int $timestamp): void
    {
        $this->assertEquals($timestamp, Helper::getTimestampFromValue($timestamp));
    }

    public function test__get_timestamp_from_value_with_invalid_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Helper::getTimestampFromValue([]);
    }

    public function test__generate_random_bytes_length(): void
    {
        $random = Helper::generateRandomBytes(10);
        $this->assertEquals(10, strlen($random));

        $random = Helper::generateRandomBytes(16);
        $this->assertEquals(16, strlen($random));

        $random = Helper::generateRandomBytes(32);
        $this->assertEquals(32, strlen($random));
    }

    public function test__generate_random_bytes_error_on_invalid_length(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Helper::generateRandomBytes(0);
    }

    public function test__generate_id(): void
    {
        $id = Helper::generateID();
        $this->assertStringStartsWith('_', $id);
        $this->assertEquals(43, strlen($id));

        $arr = [];
        for ($i = 0; $i < strlen($id); $i++) {
            $ch = $id[$i];
            $arr[$ch] = true;
        }
        $this->assertGreaterThan(8, count($arr));
    }

    public function test__validate_id_string_returns_true_for_valid_string(): void
    {
        $this->assertTrue(Helper::validateIdString('1234567890123456'));
        $this->assertTrue(Helper::validateIdString('12345678901234567890'));
    }

    public function test__validate_id_string_returns_false_for_short_string(): void
    {
        $this->assertFalse(Helper::validateIdString(''));
        $this->assertFalse(Helper::validateIdString('abc'));
        $this->assertFalse(Helper::validateIdString('123456789012345'));
    }

    public function test__validate_well_formed_uri_string_returns_false_for_empty_string(): void
    {
        $this->assertFalse(Helper::validateWellFormedUriString(''));
    }

    public function test__validate_well_formed_uri_string_returns_false_for_null(): void
    {
        $this->assertFalse(Helper::validateWellFormedUriString(null));
    }

    public function test__validate_well_formed_uri_string_returns_false_for_too_big_string(): void
    {
        $str = str_pad('', 67000, 'x');
        $this->assertFalse(Helper::validateWellFormedUriString($str));
    }

    public function test__validate_well_formed_uri_string_returns_false_for_string_with_spaces(): void
    {
        $this->assertFalse(Helper::validateWellFormedUriString('123 456 789'));
    }

    public function test__validate_well_formed_uri_string_returns_false_for_string_without_scheme(): void
    {
        $this->assertFalse(Helper::validateWellFormedUriString('example.com'));
        $this->assertFalse(Helper::validateWellFormedUriString(':example.com'));
        $this->assertFalse(Helper::validateWellFormedUriString('//:example.com'));
    }

    public function test__validate_well_formed_uri_string_returns_false_for_string_with_invalid_scheme(): void
    {
        $this->assertFalse(Helper::validateWellFormedUriString('a=b:example.com'));
        $this->assertFalse(Helper::validateWellFormedUriString('a b:example.com'));
        $this->assertFalse(Helper::validateWellFormedUriString('a&b:example.com'));
    }

    public function test__validate_well_formed_uri_string_returns_false_for_valid_string(): void
    {
        $this->assertTrue(Helper::validateWellFormedUriString('http://example.com'));
        $this->assertTrue(Helper::validateWellFormedUriString(SamlConstants::NS_ASSERTION));
        $this->assertTrue(Helper::validateWellFormedUriString(SamlConstants::PROTOCOL_SAML2));
        $this->assertTrue(Helper::validateWellFormedUriString(SamlConstants::NAME_ID_FORMAT_EMAIL));
        $this->assertTrue(Helper::validateWellFormedUriString(SamlConstants::BINDING_SAML2_HTTP_REDIRECT));
        $this->assertTrue(Helper::validateWellFormedUriString(SamlConstants::STATUS_SUCCESS));
        $this->assertTrue(Helper::validateWellFormedUriString(SamlConstants::AUTHN_CONTEXT_PASSWORD));
    }

    /** @return array<array{int, int, int, bool}> */
    public static function notBeforeProvider(): array
    {
        return [[1000, 989, 10, false], [1000, 900, 10, false], [1000, 1100, 10, true], [1000, 990, 10, true]];
    }

    #[DataProvider('notBeforeProvider')]
    public function test__validate_not_before(int $notBefore, int $now, int $allowedSecondsSkew, bool $expected): void
    {
        $this->assertEquals($expected, Helper::validateNotBefore($notBefore, $now, $allowedSecondsSkew));
    }

    /** @return array<array{int, int, int, bool}> */
    public static function notOnOrAfterProvider(): array
    {
        return [[1000, 900, 10, true], [1000, 1100, 10, false]];
    }

    #[DataProvider('notOnOrAfterProvider')]
    public function test__validate_not_on_or_after(int $notOnOrAfter, int $now, int $allowedSecondsSkew, bool $expected): void
    {
        $this->assertEquals($expected, Helper::validateNotOnOrAfter($notOnOrAfter, $now, $allowedSecondsSkew));
    }
}
