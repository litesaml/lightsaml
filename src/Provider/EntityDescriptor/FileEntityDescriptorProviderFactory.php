<?php

namespace LightSaml\Provider\EntityDescriptor;

use LightSaml\Provider\EntitiesDescriptor\FileEntitiesDescriptorProvider;

class FileEntityDescriptorProviderFactory
{
    public static function fromEntityDescriptorFile(string $filename): \LightSaml\Provider\EntityDescriptor\FileEntityDescriptorProvider
    {
        return new FileEntityDescriptorProvider($filename);
    }

    public static function fromEntitiesDescriptorFile(string $filename, string $entityId): \LightSaml\Provider\EntityDescriptor\EntitiesDescriptorEntityProvider
    {
        return new EntitiesDescriptorEntityProvider(
            new FileEntitiesDescriptorProvider($filename),
            $entityId
        );
    }
}
