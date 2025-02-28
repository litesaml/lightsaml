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

    /**
     * @return EntityDescriptor
     */
    public function getEntityDescriptor()
    {
        return $this->entityDescriptor;
    }

    /**
     * @return KeyDescriptor
     */
    public function getKeyDescriptor()
    {
        return $this->keyDescriptor;
    }

    /**
     * @return RoleDescriptor
     */
    public function getRoleDescriptor()
    {
        return $this->roleDescriptor;
    }
}
