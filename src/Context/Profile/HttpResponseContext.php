<?php

namespace LightSaml\Context\Profile;

use Psr\Http\Message\ResponseInterface;

class HttpResponseContext extends AbstractProfileContext
{
    private ?\Psr\Http\Message\ResponseInterface $response = null;

    public function getResponse(): ?\Psr\Http\Message\ResponseInterface
    {
        return $this->response;
    }

    public function setResponse(ResponseInterface $response): static
    {
        $this->response = $response;

        return $this;
    }
}
