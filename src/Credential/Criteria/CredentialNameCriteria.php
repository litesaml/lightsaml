<?php

namespace LightSaml\Credential\Criteria;

class CredentialNameCriteria implements TrustCriteriaInterface
{
    /**
     * @param string $name
     */
    public function __construct(protected $name)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }
}
