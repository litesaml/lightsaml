<?php

namespace LightSaml\Provider\EntitiesDescriptor;

use LightSaml\Context\Model\DeserializationContext;
use LightSaml\Model\Metadata\EntitiesDescriptor;

class FileEntitiesDescriptorProvider implements EntitiesDescriptorProviderInterface
{
    private ?EntitiesDescriptor $entitiesDescriptor = null;

    public function __construct(private readonly string $filename)
    {
    }

    public function get(): EntitiesDescriptor
    {
        if (null == $this->entitiesDescriptor) {
            $this->entitiesDescriptor = new EntitiesDescriptor();
            $deserializationContext = new DeserializationContext();
            $deserializationContext->getDocument()->load($this->filename);
            $this->entitiesDescriptor->deserialize($deserializationContext->getDocument()->firstChild, $deserializationContext);
        }

        return $this->entitiesDescriptor;
    }
}
