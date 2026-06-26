<?php

namespace LightSaml\Context\Profile;

use Exception;

class ExceptionContext extends AbstractProfileContext
{
    /** @var ExceptionContext|null */
    protected $nextExceptionContext;

    public function __construct(protected ?Exception $exception = null)
    {
    }

    public function getException(): ?\Exception
    {
        return $this->exception;
    }

    public function getLastException(): ?\Exception
    {
        if (null == $this->nextExceptionContext) {
            return $this->exception;
        }

        return $this->nextExceptionContext->getException();
    }

    public function getNextExceptionContext(): ?\LightSaml\Context\Profile\ExceptionContext
    {
        return $this->nextExceptionContext;
    }

    public function addException(Exception $exception): \LightSaml\Context\Profile\ExceptionContext
    {
        if ($this->exception instanceof \Exception) {
            if (null == $this->nextExceptionContext) {
                $this->nextExceptionContext = new self($exception);

                return $this->nextExceptionContext;
            } else {
                return $this->nextExceptionContext->addException($exception);
            }
        } else {
            $this->exception = $exception;
        }

        return $this;
    }
}
