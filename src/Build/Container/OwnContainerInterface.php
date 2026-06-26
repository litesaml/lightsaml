<?php

namespace LightSaml\Build\Container;

use LightSaml\Credential\CredentialInterface;
use LightSaml\Provider\EntityDescriptor\EntityDescriptorProviderInterface;

interface OwnContainerInterface
{
    public function getOwnEntityDescriptorProvider(): \LightSaml\Provider\EntityDescriptor\EntityDescriptorProviderInterface;

    /**
     * @return CredentialInterface[]
     */
    public function getOwnCredentials(): array;
}
