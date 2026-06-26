<?php

namespace LightSaml\Resolver\Endpoint\Criteria;

use LightSaml\Criteria\CriteriaInterface;

class DescriptorTypeCriteria implements CriteriaInterface
{
    /**
     * @param string $descriptorType
     */
    public function __construct(protected $descriptorType)
    {
    }

    public function getDescriptorType(): string
    {
        return $this->descriptorType;
    }
}
