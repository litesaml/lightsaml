<?php

namespace LightSaml\Store\EntityDescriptor;

use InvalidArgumentException;
use LightSaml\Model\Metadata\EntitiesDescriptor;
use LightSaml\Model\Metadata\EntityDescriptor;

class FixedEntityDescriptorStore implements EntityDescriptorStoreInterface
{
    /** @var array|EntityDescriptor[] entityId=>descriptor */
    protected $descriptors = [];

    /**
     * @param EntityDescriptor|EntitiesDescriptor $entityDescriptor
     *
     * @return FixedEntityDescriptorStore
     *
     * @throws InvalidArgumentException
     */
    public function add($entityDescriptor)
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

    /**
     * @param string $entityId
     *
     * @return EntityDescriptor|null
     */
    public function get($entityId)
    {
        return $this->descriptors[$entityId] ?? null;
    }

    /**
     * @param string $entityId
     *
     * @return bool
     */
    public function has($entityId)
    {
        return isset($this->descriptors[$entityId]);
    }

    /**
     * @return array|EntityDescriptor[]
     */
    public function all(): array
    {
        return array_values($this->descriptors);
    }
}
