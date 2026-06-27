<?php

namespace LightSaml\Error;

use Exception;
use LightSaml\Context\ContextInterface;

class LightSamlContextException extends LightSamlException
{
    public function __construct(protected ContextInterface $context, string $message = '', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getContext(): ContextInterface
    {
        return $this->context;
    }
}
