<?php

namespace LightSaml\Resolver\Endpoint\Criteria;

use LightSaml\Criteria\CriteriaInterface;

class IndexCriteria implements CriteriaInterface
{
    /**
     * @param string $index
     */
    public function __construct(protected $index)
    {
    }

    public function getIndex(): string
    {
        return $this->index;
    }
}
