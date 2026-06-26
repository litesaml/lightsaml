<?php

namespace LightSaml\Provider\EntitiesDescriptor;

use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Metadata\EntitiesDescriptor;

class FileEntitiesDescriptorProvider implements EntitiesDescriptorProviderInterface
{
    private ?\LightSaml\Model\Metadata\EntitiesDescriptor $entitiesDescriptor = null;

    public function __construct(private readonly string $filename)
    {
    }

    public function get(): \LightSaml\Model\Metadata\EntitiesDescriptor
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
