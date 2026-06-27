<?php

namespace LightSaml\Resolver\Endpoint\Criteria;

use LightSaml\Criteria\CriteriaInterface;

class IndexCriteria implements CriteriaInterface
{
    public function __construct(protected int|string $index)
    {
    }

    public function getIndex(): string
    {
        return (string) $this->index;
    }
}
