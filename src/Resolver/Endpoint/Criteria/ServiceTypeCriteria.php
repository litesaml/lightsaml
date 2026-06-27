<?php

namespace LightSaml\Resolver\Endpoint\Criteria;

use LightSaml\Criteria\CriteriaInterface;

class ServiceTypeCriteria implements CriteriaInterface
{
    /**
     * @param string $serviceType
     */
    public function __construct(protected $serviceType)
    {
    }

    public function getServiceType(): string
    {
        return $this->serviceType;
    }
}
