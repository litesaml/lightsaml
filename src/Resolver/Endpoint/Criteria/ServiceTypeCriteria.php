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

    /**
     * @return string
     */
    public function getServiceType()
    {
        return $this->serviceType;
    }
}
