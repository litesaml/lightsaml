<?php

namespace LightSaml\Validator\Model\Xsd;

use LibXMLError;
use Stringable;

class XsdError implements Stringable
{
    public const WARNING = 'Warning';
    public const ERROR = 'Error';
    public const FATAL = 'Fatal';

    private static $levelMap = [
        LIBXML_ERR_WARNING => self::WARNING,
        LIBXML_ERR_ERROR => self::ERROR,
        LIBXML_ERR_FATAL => self::FATAL,
    ];

    /**
     * @deprecated
     *
     * @return XsdError
     */
    public static function fromLibXMLError(LibXMLError $error)
    {
        return new self(
            self::$levelMap[$error->level] ?? 'Unknown',
            $error->code,
            $error->message,
            $error->line,
            $error->column
        );
    }

    /**
     * @param string $level
     * @param string $code
     * @param string $message
     * @param string $line
     * @param string $column
     */
    public function __construct(private $level, private $code, private $message, private $line, private $column)
    {
    }

    /**
     * @return string
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @return string
     */
    public function getColumn()
    {
        return $this->column;
    }

    public function __toString(): string
    {
        return sprintf(
            '%s %s: %s on line %s column %s',
            $this->level,
            $this->code,
            trim($this->message),
            $this->line,
            $this->column
        );
    }
}
