<?php

namespace LightSaml\Credential\Criteria;

class PublicKeyThumbprintCriteria implements TrustCriteriaInterface
{
    /** @var string */
    private $thumbprint;

    /**
     * @param string $thumbprint
     */
    public function __construct($thumbprint)
    {
        $this->thumbprint = $thumbprint;
    }

    /**
     * @return string
     */
    public function getThumbprint()
    {
        return $this->thumbprint;
    }
}
