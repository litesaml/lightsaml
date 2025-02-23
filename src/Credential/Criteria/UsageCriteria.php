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

    /**
     * @return string
     */
    public function getUsage()
    {
        return $this->usage;
    }
}
