<?php

namespace LightSaml\Provider\EntitiesDescriptor;

use LightSaml\Model\Metadata\EntitiesDescriptor;

interface EntitiesDescriptorProviderInterface
{
    public function get(): \LightSaml\Model\Metadata\EntitiesDescriptor;
}
