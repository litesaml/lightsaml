<?php

namespace LightSaml\Provider\EntityDescriptor;

use LightSaml\Model\Metadata\EntityDescriptor;

class FixedEntityDescriptorProvider implements EntityDescriptorProviderInterface
{
    public function __construct(protected EntityDescriptor $entityDescriptor)
    {
    }

    public function get(): \LightSaml\Model\Metadata\EntityDescriptor
    {
        return $this->entityDescriptor;
    }
}
