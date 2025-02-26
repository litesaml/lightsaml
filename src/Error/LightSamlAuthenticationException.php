<?php

namespace LightSaml\Error;

use Exception;
use LightSaml\Model\Protocol\Response;
use LightSaml\Model\Protocol\StatusResponse;

class LightSamlAuthenticationException extends LightSamlValidationException
{
    /**
     * @param string $message
     * @param int    $code
     */
    public function __construct(protected StatusResponse $response, $message = '', $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}
