<?php

namespace LightSaml\Credential\Criteria;

class UsageCriteria implements TrustCriteriaInterface
{
    /**
     * @param string $usage
     */
    public function __construct(protected $usage)
    {
    }

    public function getUsage(): string
    {
        return $this->usage;
    }
}
