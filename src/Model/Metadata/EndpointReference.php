<?php

namespace LightSaml\Model\Metadata;

class EndpointReference
{
    public function __construct(protected \LightSaml\Model\Metadata\EntityDescriptor $entityDescriptor, protected \LightSaml\Model\Metadata\RoleDescriptor $descriptor, protected \LightSaml\Model\Metadata\Endpoint $endpoint)
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
     * @return RoleDescriptor
     */
    public function getDescriptor()
    {
        return $this->descriptor;
    }

    /**
     * @return Endpoint
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }
}
