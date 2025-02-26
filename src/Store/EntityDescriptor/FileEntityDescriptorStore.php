<?php

namespace LightSaml\Store\EntityDescriptor;

use LightSaml\Error\LightSamlXmlException;
use LightSaml\Model\Metadata\EntitiesDescriptor;
use LightSaml\Model\Metadata\EntityDescriptor;

class FileEntityDescriptorStore implements EntityDescriptorStoreInterface
{
    /** @var EntityDescriptor|EntitiesDescriptor */
    private $object;

    /**
     * @param string $filename
     */
    public function __construct(private $filename)
    {
    }

    /**
     * @param string $entityId
     *
     * @return EntityDescriptor|null
     */
    public function get($entityId)
    {
        if (null == $this->object) {
            $this->load();
        }

        if ($this->object instanceof EntityDescriptor) {
            if ($this->object->getEntityID() == $entityId) {
                return $this->object;
            } else {
                return;
            }
        } else {
            return $this->object->getByEntityId($entityId);
        }
    }

    /**
     * @param string $entityId
     *
     * @return bool
     */
    public function has($entityId)
    {
        return null != $this->get($entityId);
    }

    /**
     * @return array|EntityDescriptor[]
     */
    public function all()
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

    private function load()
    {
        try {
            $this->object = EntityDescriptor::load($this->filename);
        } catch (LightSamlXmlException) {
            $this->object = EntitiesDescriptor::load($this->filename);
        }
    }
}
