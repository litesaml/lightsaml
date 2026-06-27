<?php

namespace LightSaml\Validator\Model\Xsd;

use LibXMLError;
use Stringable;

class XsdError implements Stringable
{
    public const WARNING = 'Warning';
    public const ERROR = 'Error';
    public const FATAL = 'Fatal';

    /** @var array<int, string> */
    private static array $levelMap = [
        LIBXML_ERR_WARNING => self::WARNING,
        LIBXML_ERR_ERROR => self::ERROR,
        LIBXML_ERR_FATAL => self::FATAL,
    ];

    /**
     * @deprecated
     */
    public static function fromLibXMLError(LibXMLError $error): self
    {
        return new self(
            self::$levelMap[$error->level] ?? 'Unknown',
            (string) $error->code,
            $error->message,
            (string) $error->line,
            (string) $error->column
        );
    }

    public function __construct(private readonly string $level, private readonly string $code, private readonly string $message, private readonly string $line, private readonly string $column)
    {
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getLine(): string
    {
        return $this->line;
    }

    public function getColumn(): string
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
