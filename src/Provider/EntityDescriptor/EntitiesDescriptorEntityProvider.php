<?php

namespace LightSaml\Provider\EntityDescriptor;

use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\Provider\EntitiesDescriptor\EntitiesDescriptorProviderInterface;

class EntitiesDescriptorEntityProvider implements EntityDescriptorProviderInterface
{
    /** @var EntityDescriptor */
    private $entityDescriptor;

    /**
     * @param string $entityId
     */
    public function __construct(private readonly EntitiesDescriptorProviderInterface $entitiesDescriptorProvider, private $entityId)
    {
    }

    /**
     * @return EntityDescriptor
     */
    public function get()
    {
        if (null == $this->entityDescriptor) {
            $this->entityDescriptor = $this->entitiesDescriptorProvider->get()->getByEntityId($this->entityId);
        }

        return $this->entityDescriptor;
    }
}
