<?php

namespace LightSaml\Context\Profile;

use Psr\Http\Message\ResponseInterface;

class HttpResponseContext extends AbstractProfileContext
{
    private ?ResponseInterface $response = null;

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    public function setResponse(ResponseInterface $response): static
    {
        $this->response = $response;

        return $this;
    }
}
