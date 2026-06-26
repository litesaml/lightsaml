<?php

namespace LightSaml\Provider\EntityDescriptor;

use LightSaml\Model\Metadata\EntityDescriptor;

interface EntityDescriptorProviderInterface
{
    public function get(): \LightSaml\Model\Metadata\EntityDescriptor;
}
