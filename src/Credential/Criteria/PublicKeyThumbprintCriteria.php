<?php

namespace LightSaml\Credential\Criteria;

class PublicKeyThumbprintCriteria implements TrustCriteriaInterface
{
    public function __construct(private readonly string $thumbprint)
    {
    }

    public function getThumbprint(): string
    {
        return $this->thumbprint;
    }
}
