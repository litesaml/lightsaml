<?php

namespace LightSaml\Credential\Context;

use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\Model\Metadata\KeyDescriptor;
use LightSaml\Model\Metadata\RoleDescriptor;

class MetadataCredentialContext implements CredentialContextInterface
{
    public function __construct(protected KeyDescriptor $keyDescriptor, protected RoleDescriptor $roleDescriptor, protected EntityDescriptor $entityDescriptor)
    {
    }

    public function getEntityDescriptor(): EntityDescriptor
    {
        return $this->entityDescriptor;
    }

    public function getKeyDescriptor(): KeyDescriptor
    {
        return $this->keyDescriptor;
    }

    public function getRoleDescriptor(): RoleDescriptor
    {
        return $this->roleDescriptor;
    }
}
