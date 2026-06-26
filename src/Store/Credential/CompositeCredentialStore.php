<?php

namespace LightSaml\Store\Credential;

use LightSaml\Credential\CredentialInterface;

class CompositeCredentialStore implements CredentialStoreInterface
{
    /** @var CredentialStoreInterface[] */
    protected $stores = [];

    /**
     * @return CredentialInterface[]
     */
    public function getByEntityId(string $entityId): array
    {
        $result = [];
        foreach ($this->stores as $store) {
            $result = array_merge($result, $store->getByEntityId($entityId));
        }

        return $result;
    }

    public function add(CredentialStoreInterface $store): static
    {
        $this->stores[] = $store;

        return $this;
    }
}
