<?php

namespace LightSaml\Store\EntityDescriptor;

use LightSaml\Error\LightSamlXmlException;
use LightSaml\Model\Metadata\EntitiesDescriptor;
use LightSaml\Model\Metadata\EntityDescriptor;

class FileEntityDescriptorStore implements EntityDescriptorStoreInterface
{
    /**  */
    private \LightSaml\Model\Metadata\EntityDescriptor|\LightSaml\Model\Metadata\EntitiesDescriptor|null $object = null;

    public function __construct(private string $filename)
    {
    }

    public function get(string $entityId): ?EntityDescriptor
    {
        if (null == $this->object) {
            $this->load();
        }

        if ($this->object instanceof EntityDescriptor) {
            if ($this->object->getEntityID() === $entityId) {
                return $this->object;
            } else {
                return null;
            }
        } else {
            return $this->object->getByEntityId($entityId);
        }
    }

    public function has(string $entityId): bool
    {
        return null != $this->get($entityId);
    }

    /** @return EntityDescriptor[] */
    public function all(): array
    {
        if (null == $this->object) {
            $this->load();
        }

        if ($this->object instanceof EntityDescriptor) {
            return [$this->object];
        } else {
            return $this->object->getAllEntityDescriptors();
        }
    }

    private function load(): void
    {
        try {
            $this->object = EntityDescriptor::load($this->filename);
        } catch (LightSamlXmlException) {
            $this->object = EntitiesDescriptor::load($this->filename);
        }
    }
}
