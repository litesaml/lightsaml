<?php

namespace LightSaml\Context\Profile;

use Psr\Http\Message\ServerRequestInterface;

class HttpRequestContext extends AbstractProfileContext
{
    /** @var ServerRequestInterface */
    private $request;

    /**
     * @return ServerRequestInterface|null
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return HttpRequestContext
     */
    public function setRequest(ServerRequestInterface $request)
    {
        $this->request = $request;

        return $this;
    }
}
