<?php

namespace LightSaml\Context\Profile;

use LightSaml\Model\Metadata\Endpoint;

class EndpointContext extends AbstractProfileContext
{
    private ?\LightSaml\Model\Metadata\Endpoint $endpoint = null;

    public function getEndpoint(): ?\LightSaml\Model\Metadata\Endpoint
    {
        return $this->endpoint;
    }

    public function setEndpoint(Endpoint $endpoint): static
    {
        $this->endpoint = $endpoint;

        return $this;
    }
}
