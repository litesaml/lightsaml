<?php

namespace LightSaml\Bridge\Pimple\Container;

use LightSaml\Build\Container\OwnContainerInterface;
use LightSaml\Credential\CredentialInterface;
use LightSaml\Provider\EntityDescriptor\EntityDescriptorProviderInterface;

class OwnContainer extends AbstractPimpleContainer implements OwnContainerInterface
{
    public const OWN_ENTITY_DESCRIPTOR_PROVIDER = 'lightsaml.container.own_entity_descriptor_provider';
    public const OWN_CREDENTIALS = 'lightsaml.container.own_credentials';

    /**
     * @return EntityDescriptorProviderInterface
     */
    public function getOwnEntityDescriptorProvider()
    {
        return $this->pimple[self::OWN_ENTITY_DESCRIPTOR_PROVIDER];
    }

    /**
     * @return CredentialInterface[]
     */
    public function getOwnCredentials()
    {
        return $this->pimple[self::OWN_CREDENTIALS];
    }
}
