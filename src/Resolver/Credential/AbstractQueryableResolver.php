<?php

namespace LightSaml\Resolver\Credential;

abstract class AbstractQueryableResolver implements CredentialResolverInterface
{
    public function query(): \LightSaml\Resolver\Credential\CredentialResolverQuery
    {
        return new CredentialResolverQuery($this);
    }
}
