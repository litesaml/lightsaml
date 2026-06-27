<?php

namespace LightSaml\Store\TrustOptions;

use LightSaml\Meta\TrustOptions\TrustOptions;

class CompositeTrustOptionsStore implements TrustOptionsStoreInterface
{
    /** @var TrustOptionsStoreInterface[] */
    private array $children = [];

    /**
     * @param TrustOptionsStoreInterface[] $stores
     */
    public function __construct(array $stores = [])
    {
        foreach ($stores as $store) {
            $this->add($store);
        }
    }

    public function add(TrustOptionsStoreInterface $store): static
    {
        $this->children[] = $store;

        return $this;
    }

    public function get(string $entityId): ?TrustOptions
    {
        foreach ($this->children as $store) {
            $result = $store->get($entityId);
            if ($result) {
                return $result;
            }
        }

        return null;
    }

    public function has(string $entityId): bool
    {
        foreach ($this->children as $store) {
            if ($store->has($entityId)) {
                return true;
            }
        }

        return false;
    }
}
