<?php

namespace LightSaml\Build\Container;

use LightSaml\Store\Credential\CredentialStoreInterface;

interface CredentialContainerInterface
{
    public function getCredentialStore(): CredentialStoreInterface;
}
