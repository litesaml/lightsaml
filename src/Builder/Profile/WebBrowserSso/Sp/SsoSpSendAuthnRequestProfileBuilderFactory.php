<?php

namespace LightSaml\Builder\Profile\WebBrowserSso\Sp;

use LightSaml\Build\Container\BuildContainerInterface;

class SsoSpSendAuthnRequestProfileBuilderFactory
{
    public function __construct(private readonly BuildContainerInterface $buildContainer)
    {
    }

    /**
     * @param string $idpEntityId
     *
     * @return SsoSpSendAuthnRequestProfileBuilder
     */
    public function get($idpEntityId)
    {
        return new SsoSpSendAuthnRequestProfileBuilder($this->buildContainer, $idpEntityId);
    }
}
