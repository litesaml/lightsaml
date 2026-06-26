<?php

namespace LightSaml\Store\Id;

use DateTime;

class NullIdStore implements IdStoreInterface
{
    
    public function set(string $entityId, string $id, DateTime $expiryTime): void
    {
    }

    public function has(string $entityId, string $id): bool
    {
        return false;
    }
}
