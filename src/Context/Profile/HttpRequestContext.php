<?php

namespace LightSaml\Context\Profile;

use Psr\Http\Message\ServerRequestInterface;

class HttpRequestContext extends AbstractProfileContext
{
    private ?\Psr\Http\Message\ServerRequestInterface $request = null;

    public function getRequest(): ?\Psr\Http\Message\ServerRequestInterface
    {
        return $this->request;
    }

    public function setRequest(ServerRequestInterface $request): static
    {
        $this->request = $request;

        return $this;
    }
}
