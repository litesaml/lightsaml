<?php

namespace LightSaml\Error;

use Exception;
use LightSaml\Context\ContextInterface;

class LightSamlContextException extends LightSamlException
{
    /**
     * @param string $message
     * @param int    $code
     */
    public function __construct(protected ContextInterface $context, $message = '', $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return ContextInterface
     */
    public function getContext()
    {
        return $this->context;
    }
}
