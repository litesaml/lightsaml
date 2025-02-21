<?php

namespace LightSaml\Resolver\Endpoint\Criteria;

use LightSaml\Criteria\CriteriaInterface;

class LocationCriteria implements CriteriaInterface
{
    /**
     * @param string $location
     */
    public function __construct(protected $location)
    {
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }
}
