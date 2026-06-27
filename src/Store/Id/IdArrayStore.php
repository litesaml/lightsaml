<?php

namespace LightSaml\Store\Id;

use DateTime;

class IdArrayStore implements IdStoreInterface
{
    protected array $store = [];

    public function set(string $entityId, string $id, DateTime $expiryTime): void
    {
        if (false == isset($this->store[$entityId])) {
            $this->store[$entityId] = [];
        }
        $this->store[$entityId][$id] = $expiryTime;
    }

    public function has(string $entityId, string $id): bool
    {
        return isset($this->store[$entityId][$id]);
    }
}
