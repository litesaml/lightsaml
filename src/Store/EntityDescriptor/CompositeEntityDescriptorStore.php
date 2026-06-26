<?php

namespace LightSaml\Store\EntityDescriptor;

use LightSaml\Model\Metadata\EntityDescriptor;

class CompositeEntityDescriptorStore implements EntityDescriptorStoreInterface
{
    /** @var EntityDescriptorStoreInterface[] */
    private array $children = [];

    /**
     * @param EntityDescriptorStoreInterface[] $stores
     */
    public function __construct(array $stores = [])
    {
        foreach ($stores as $store) {
            $this->add($store);
        }
    }

    public function add(EntityDescriptorStoreInterface $store): static
    {
        $this->children[] = $store;

        return $this;
    }

    
    public function get(string $entityId): ?\LightSaml\Model\Metadata\EntityDescriptor
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

    /**
     * @return array|EntityDescriptor[]
     */
    public function all(): array
    {
        $result = [];
        foreach ($this->children as $store) {
            $result = array_merge($result, $store->all());
        }

        return $result;
    }
}
