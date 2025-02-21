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

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
