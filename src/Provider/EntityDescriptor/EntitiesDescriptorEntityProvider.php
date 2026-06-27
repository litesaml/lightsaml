<?php

namespace LightSaml\Provider\EntityDescriptor;

use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\Provider\EntitiesDescriptor\EntitiesDescriptorProviderInterface;

class EntitiesDescriptorEntityProvider implements EntityDescriptorProviderInterface
{
    private ?EntityDescriptor $entityDescriptor = null;

    public function __construct(private readonly EntitiesDescriptorProviderInterface $entitiesDescriptorProvider, private readonly string $entityId)
    {
    }

    public function get(): EntityDescriptor
    {
        if (null == $this->entityDescriptor) {
            $this->entityDescriptor = $this->entitiesDescriptorProvider->get()->getByEntityId($this->entityId);
        }

        return $this->entityDescriptor;
    }
}
