<?php

namespace LightSaml\Provider\EntitiesDescriptor;

use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Metadata\EntitiesDescriptor;

class FileEntitiesDescriptorProvider implements EntitiesDescriptorProviderInterface
{
    /** @var EntitiesDescriptor */
    private $entitiesDescriptor;

    /**
     * @param string $filename
     */
    public function __construct(private $filename)
    {
    }

    /**
     * @return EntitiesDescriptor
     */
    public function get()
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
