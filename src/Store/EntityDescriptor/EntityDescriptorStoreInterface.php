<?php

namespace LightSaml\Store\EntityDescriptor;

use LightSaml\Model\Metadata\EntityDescriptor;

interface EntityDescriptorStoreInterface
{
    public function get(string $entityId): ?EntityDescriptor;

    public function has(string $entityId): bool;

    /**
     * @return array|EntityDescriptor[]
     */
    public function all(): array;
}
