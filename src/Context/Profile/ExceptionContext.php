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

    /**
     * @return Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @return Exception|null
     */
    public function getLastException()
    {
        if (null == $this->nextExceptionContext) {
            return $this->exception;
        }

        return $this->nextExceptionContext->getException();
    }

    /**
     * @return ExceptionContext|null
     */
    public function getNextExceptionContext()
    {
        return $this->nextExceptionContext;
    }

    /**
     * @return ExceptionContext
     */
    public function addException(Exception $exception)
    {
        if ($this->exception) {
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
