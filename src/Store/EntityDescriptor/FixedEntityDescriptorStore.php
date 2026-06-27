<?php

namespace LightSaml\Store\EntityDescriptor;

use InvalidArgumentException;
use LightSaml\Model\Metadata\EntitiesDescriptor;
use LightSaml\Model\Metadata\EntityDescriptor;

class FixedEntityDescriptorStore implements EntityDescriptorStoreInterface
{
    /** @var EntityDescriptor[] entityId=>descriptor */
    protected array $descriptors = [];

    /**
     *
     * @throws InvalidArgumentException
     */
    public function add(\LightSaml\Model\Metadata\EntityDescriptor|\LightSaml\Model\Metadata\EntitiesDescriptor $entityDescriptor): static
    {
        if ($entityDescriptor instanceof EntityDescriptor) {
            if (false == $entityDescriptor->getEntityID()) {
                throw new InvalidArgumentException('EntityDescriptor must have entityId set');
            }
            $this->descriptors[$entityDescriptor->getEntityID()] = $entityDescriptor;
        } elseif ($entityDescriptor instanceof EntitiesDescriptor) {
            foreach ($entityDescriptor->getAllItems() as $item) {
                $this->add($item);
            }
        } else {
            throw new InvalidArgumentException('Expected EntityDescriptor or EntitiesDescriptor');
        }

        return $this;
    }

    
    public function get(string $entityId): ?\LightSaml\Model\Metadata\EntityDescriptor
    {
        return $this->descriptors[$entityId] ?? null;
    }

    public function has(string $entityId): bool
    {
        return isset($this->descriptors[$entityId]);
    }

    /** @return EntityDescriptor[] */
    public function all(): array
    {
        return array_values($this->descriptors);
    }
}
