<?php

namespace LightSaml\Store\Credential;

use LightSaml\Credential\CredentialInterface;

class StaticCredentialStore implements CredentialStoreInterface
{
    /** @var array<string, CredentialInterface[]> */
    protected array $credentials = [];

    /**
     * @return CredentialInterface[]
     */
    public function getByEntityId(string $entityId): array
    {
        $this->checkEntityIdExistence($entityId);

        return $this->credentials[$entityId];
    }

    public function has(string $entityId): bool
    {
        return array_key_exists($entityId, $this->credentials);
    }

    public function add(CredentialInterface $credential): static
    {
        $this->checkEntityIdExistence($credential->getEntityId());

        $this->credentials[$credential->getEntityId()][] = $credential;

        return $this;
    }

    private function checkEntityIdExistence(string $entityId): void
    {
        if (false == $this->has($entityId)) {
            $this->credentials[$entityId] = [];
        }
    }
}
