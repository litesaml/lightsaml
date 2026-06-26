<?php

namespace LightSaml\Context\Profile;

use Psr\Http\Message\ResponseInterface;

class HttpResponseContext extends AbstractProfileContext
{
    /** @var ResponseInterface */
    private $response;

    /**
     * @return ResponseInterface|null
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return HttpResponseContext
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;

        return $this;
    }
}
