<?php

namespace LightSaml\Store\Credential;

use LightSaml\Credential\CredentialInterface;

interface CredentialStoreInterface
{
    /**
     * @return CredentialInterface[]
     */
    public function getByEntityId(string $entityId): array;
}
