<?php

namespace LightSaml\Context\Profile;

use Psr\Http\Message\ServerRequestInterface;

class HttpRequestContext extends AbstractProfileContext
{
    private ?ServerRequestInterface $request = null;

    public function getRequest(): ?ServerRequestInterface
    {
        return $this->request;
    }

    public function setRequest(ServerRequestInterface $request): static
    {
        $this->request = $request;

        return $this;
    }
}
