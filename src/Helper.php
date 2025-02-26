<?php

namespace LightSaml;

use DateInterval;
use DateTime;
use Exception;
use InvalidArgumentException;

final class Helper
{
    public const TIME_FORMAT = 'Y-m-d\TH:i:s\Z';

    /**
     * @param string $duration
     */
    public static function validateDurationString($duration)
    {
        if ($duration) {
            try {
                new DateInterval((string) $duration);
            } catch (Exception $ex) {
                throw new InvalidArgumentException(sprintf("Invalid duration '%s' format", $duration), 0, $ex);
            }
        }
    }

    /**
     * @param int $time
     */
    public static function time2string($time): string
    {
        return gmdate('Y-m-d\TH:i:s\Z', $time);
    }

    /**
     * @param int|string|DateTime $value
     *
     * @return int
     *
     * @throws InvalidArgumentException
     */
    public static function getTimestampFromValue($value)
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
     * @param string $time
     *
     * @return int
     *
     * @throws InvalidArgumentException
     */
    public static function parseSAMLTime($time): int|false
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
     * @param int $length
     *
     * @throws InvalidArgumentException
     */
    public static function generateRandomBytes($length): string
    {
        $length = intval($length);
        if ($length <= 0) {
            throw new InvalidArgumentException();
        }

        return random_bytes($length);
    }

    /**
     * @param string $bytes
     */
    public static function stringToHex($bytes): string
    {
        return bin2hex($bytes);
    }

    /**
     * @return string
     */
    public static function generateID()
    {
        return '_' . self::stringToHex(self::generateRandomBytes(21));
    }

    /**
     * Is ID element at least 128 bits in length (SAML2.0 standard section 1.3.4).
     *
     * @param string $id
     *
     * @return bool
     */
    public static function validateIdString($id)
    {
        return is_string($id) && strlen(trim($id)) >= 16;
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public static function validateRequiredString($value)
    {
        return is_string($value) && strlen(trim($value)) > 0;
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public static function validateOptionalString($value)
    {
        return null === $value || self::validateRequiredString($value);
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public static function validateWellFormedUriString($value)
    {
        if (is_null($value)) {
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
     *
     * @param int $notBefore
     * @param int $now
     * @param int $allowedSecondsSkew
     *
     * @return bool
     */
    public static function validateNotBefore($notBefore, $now, $allowedSecondsSkew)
    {
        return null == $notBefore || (($notBefore - $allowedSecondsSkew) <= $now);
    }

    /**
     * @param int $notOnOrAfter
     * @param int $now
     * @param int $allowedSecondsSkew
     *
     * @return bool
     */
    public static function validateNotOnOrAfter($notOnOrAfter, $now, $allowedSecondsSkew)
    {
        return null == $notOnOrAfter || ($now < ($notOnOrAfter + $allowedSecondsSkew));
    }
}
