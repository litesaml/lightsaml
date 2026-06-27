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

    public function getLocation(): string
    {
        return $this->location;
    }
}
