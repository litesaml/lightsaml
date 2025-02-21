<?php

namespace LightSaml\Provider\EntityDescriptor;

use LightSaml\Model\Metadata\EntityDescriptor;

class FixedEntityDescriptorProvider implements EntityDescriptorProviderInterface
{
    public function __construct(protected \LightSaml\Model\Metadata\EntityDescriptor $entityDescriptor)
    {
    }

    /**
     * @return EntityDescriptor
     */
    public function get()
    {
        return $this->entityDescriptor;
    }
}
