<?php

namespace LightSaml\Build\Container;

use LightSaml\Credential\CredentialInterface;
use LightSaml\Provider\EntityDescriptor\EntityDescriptorProviderInterface;

interface OwnContainerInterface
{
    /**
     * @return EntityDescriptorProviderInterface
     */
    public function getOwnEntityDescriptorProvider();

    /**
     * @return CredentialInterface[]
     */
    public function getOwnCredentials();
}
