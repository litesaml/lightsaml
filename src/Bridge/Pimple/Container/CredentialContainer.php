<?php

namespace LightSaml\Bridge\Pimple\Container;

use LightSaml\Build\Container\CredentialContainerInterface;
use LightSaml\Store\Credential\CredentialStoreInterface;

/**
 * @deprecated 5.0.0 No longer used by internal code and not recommended
 */
class CredentialContainer extends AbstractPimpleContainer implements CredentialContainerInterface
{
    public const CREDENTIAL_STORE = 'lightsaml.container.credential_store';

    /**
     * @return CredentialStoreInterface
     */
    public function getCredentialStore()
    {
        return $this->pimple[self::CREDENTIAL_STORE];
    }
}
