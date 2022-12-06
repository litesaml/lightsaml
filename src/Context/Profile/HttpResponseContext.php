<?php

namespace LightSaml\Context\Profile;

use Symfony\Component\HttpFoundation\Response;

class HttpResponseContext extends AbstractProfileContext
{
    /** @var Response */
    private $response;

    /**
     * @return Response|null
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return HttpResponseContext
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;

        return $this;
    }
}
