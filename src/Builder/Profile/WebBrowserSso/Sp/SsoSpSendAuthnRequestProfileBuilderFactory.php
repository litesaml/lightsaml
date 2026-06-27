<?php

namespace LightSaml\Builder\Profile\WebBrowserSso\Sp;

use LightSaml\Build\Container\BuildContainerInterface;

class SsoSpSendAuthnRequestProfileBuilderFactory
{
    public function __construct(private readonly BuildContainerInterface $buildContainer)
    {
    }

    public function get(string $idpEntityId): SsoSpSendAuthnRequestProfileBuilder
    {
        return new SsoSpSendAuthnRequestProfileBuilder($this->buildContainer, $idpEntityId);
    }
}
