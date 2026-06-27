<?php

namespace LightSaml\Model\Metadata;

class EndpointReference
{
    public function __construct(protected EntityDescriptor $entityDescriptor, protected RoleDescriptor $descriptor, protected Endpoint $endpoint)
    {
    }

    public function getEntityDescriptor(): EntityDescriptor
    {
        return $this->entityDescriptor;
    }

    public function getDescriptor(): RoleDescriptor
    {
        return $this->descriptor;
    }

    public function getEndpoint(): Endpoint
    {
        return $this->endpoint;
    }
}
