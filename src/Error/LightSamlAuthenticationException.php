<?php

namespace LightSaml\Error;

use Exception;
use LightSaml\Model\Protocol\StatusResponse;

class LightSamlAuthenticationException extends LightSamlValidationException
{
    public function __construct(protected StatusResponse $response, string $message = '', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getResponse(): StatusResponse
    {
        return $this->response;
    }
}
