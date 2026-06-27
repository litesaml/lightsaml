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

    public function getAlgorithm(): string
    {
        return $this->algorithm;
    }
}
