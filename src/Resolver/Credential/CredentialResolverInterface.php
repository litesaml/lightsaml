<?php

namespace LightSaml\Resolver\Credential;

use LightSaml\Credential\CredentialInterface;
use LightSaml\Criteria\CriteriaSet;

interface CredentialResolverInterface
{
    /**
     * @param array|CredentialInterface[] $arrCredentials
     *
     * @return array|CredentialInterface[]
     */
    public function resolve(CriteriaSet $criteriaSet, array $arrCredentials = []): array;

    public function query(): CredentialResolverQuery;
}
