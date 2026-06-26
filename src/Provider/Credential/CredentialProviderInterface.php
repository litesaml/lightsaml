<?php

namespace LightSaml\Provider\Credential;

use LightSaml\Credential\CredentialInterface;

interface CredentialProviderInterface
{
    public function get(): \LightSaml\Credential\CredentialInterface;
}
