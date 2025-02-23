<?php

namespace LightSaml\Credential\Criteria;

class PublicKeyThumbprintCriteria implements TrustCriteriaInterface
{
    /**
     * @param string $thumbprint
     */
    public function __construct(private $thumbprint)
    {
    }

    /**
     * @return string
     */
    public function getThumbprint()
    {
        return $this->thumbprint;
    }
}
