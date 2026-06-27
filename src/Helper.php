<?php

namespace LightSaml;

use DateInterval;
use DateTime;
use Exception;
use InvalidArgumentException;

final class Helper
{
    public const TIME_FORMAT = 'Y-m-d\TH:i:s\Z';

    public static function validateDurationString(string $duration): void
    {
        if ($duration !== '' && $duration !== '0') {
            try {
                new DateInterval($duration);
            } catch (Exception $ex) {
                throw new InvalidArgumentException(sprintf("Invalid duration '%s' format", $duration), 0, $ex);
            }
        }
    }

    public static function time2string(int $time): string
    {
        return gmdate('Y-m-d\TH:i:s\Z', $time);
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function getTimestampFromValue(mixed $value): int|false
    {
        if (is_string($value)) {
            return self::parseSAMLTime($value);
        } elseif ($value instanceof DateTime) {
            return $value->getTimestamp();
        } elseif (is_int($value)) {
            return $value;
        } else {
            throw new InvalidArgumentException();
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function parseSAMLTime(string $time): int|false
    {
        $matches = [];
        if (
            0 == preg_match(
                '/^(\\d\\d\\d\\d)-(\\d\\d)-(\\d\\d)T(\\d\\d):(\\d\\d):(\\d\\d)(?:\\.\\d+)?(Z|[+-]\\d\\d:\\d\\d)$/D',
                $time,
                $matches
            )
        ) {
            throw new InvalidArgumentException('Invalid SAML2 timestamp: ' . $time);
        }

        return strtotime($time);
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function generateRandomBytes(int $length): string
    {
        if ($length <= 0) {
            throw new InvalidArgumentException();
        }

        return random_bytes($length);
    }

    public static function stringToHex(string $bytes): string
    {
        return bin2hex($bytes);
    }

    public static function generateID(): string
    {
        return '_' . self::stringToHex(self::generateRandomBytes(21));
    }

    /**
     * Is ID element at least 128 bits in length (SAML2.0 standard section 1.3.4).
     */
    public static function validateIdString(?string $id): bool
    {
        return null !== $id && strlen(trim($id)) >= 16;
    }

    public static function validateWellFormedUriString(?string $value): bool
    {
        if (null === $value) {
            return false;
        }

        $value = trim($value);

        if ('' === $value || strlen($value) > 65520) {
            return false;
        }

        if (preg_match('|\s|', $value)) {
            return false;
        }

        $parts = parse_url($value);
        if (isset($parts['scheme'])) {
            if ($parts['scheme'] !== rawurlencode($parts['scheme'])) {
                return false;
            }
        } else {
            return false;
        }

        return true;
    }

    /**
     * Returns `true` when `$now` is on or after `$notBefore`.
     */
    public static function validateNotBefore(?int $notBefore, int $now, int $allowedSecondsSkew): bool
    {
        return null == $notBefore || (($notBefore - $allowedSecondsSkew) <= $now);
    }

    public static function validateNotOnOrAfter(?int $notOnOrAfter, int $now, int $allowedSecondsSkew): bool
    {
        return null === $notOnOrAfter || ($now < ($notOnOrAfter + $allowedSecondsSkew));
    }
}
