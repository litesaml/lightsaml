<?php

namespace LightSaml\Provider\EntityDescriptor;

use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Metadata\EntityDescriptor;

class FileEntityDescriptorProvider implements EntityDescriptorProviderInterface
{
    private ?\LightSaml\Model\Metadata\EntityDescriptor $entityDescriptor = null;

    public function __construct(private readonly string $filename)
    {
    }

    public function get(): \LightSaml\Model\Metadata\EntityDescriptor
    {
        if (null == $this->entityDescriptor) {
            $this->entityDescriptor = new EntityDescriptor();
            $deserializationContext = new DeserializationContext();
            $deserializationContext->getDocument()->load($this->filename);
            $this->entityDescriptor->deserialize($deserializationContext->getDocument()->firstChild, $deserializationContext);
        }

        return $this->entityDescriptor;
    }
}
