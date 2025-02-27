<?php

namespace LightSaml\Model\Metadata;

class EndpointReference
{
    public function __construct(protected EntityDescriptor $entityDescriptor, protected RoleDescriptor $descriptor, protected Endpoint $endpoint)
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
