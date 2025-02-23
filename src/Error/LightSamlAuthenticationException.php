<?php

namespace LightSaml\Error;

use LightSaml\Model\Protocol\StatusResponse;

class LightSamlAuthenticationException extends LightSamlValidationException
{
    /**
     * @param string     $message
     * @param int        $code
     * @param \Exception $previous
     */
    public function __construct(protected \LightSaml\Model\Protocol\StatusResponse $response, $message = '', $code = 0, ?\Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return \LightSaml\Model\Protocol\Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}
