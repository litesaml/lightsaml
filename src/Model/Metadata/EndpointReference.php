<?php

namespace LightSaml\Model\Metadata;

class EndpointReference
{
    public function __construct(protected EntityDescriptor $entityDescriptor, protected RoleDescriptor $descriptor, protected Endpoint $endpoint)
    {
    }

    public function getEntityDescriptor(): \LightSaml\Model\Metadata\EntityDescriptor
    {
        return $this->entityDescriptor;
    }

    public function getDescriptor(): \LightSaml\Model\Metadata\RoleDescriptor
    {
        return $this->descriptor;
    }

    public function getEndpoint(): \LightSaml\Model\Metadata\Endpoint
    {
        return $this->endpoint;
    }
}
