<?php

namespace LightSaml\Response;

class RedirectResponse
{
    protected $statusCode = 302;

    protected $destination;

    public function __construct(string $destination)
    {
        $this->destination = $destination;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getDestination()
    {
        return $this->destination;
    }
}
