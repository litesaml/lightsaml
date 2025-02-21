<?php

namespace LightSaml\Credential\Criteria;

class AlgorithmCriteria implements TrustCriteriaInterface
{
    /**
     * @param string $algorithm
     */
    public function __construct(protected $algorithm)
    {
    }

    /**
     * @return string
     */
    public function getAlgorithm()
    {
        return $this->algorithm;
    }
}
