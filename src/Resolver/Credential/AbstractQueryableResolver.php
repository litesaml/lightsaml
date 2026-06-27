<?php

namespace LightSaml\Resolver\Credential;

abstract class AbstractQueryableResolver implements CredentialResolverInterface
{
    public function query(): CredentialResolverQuery
    {
        return new CredentialResolverQuery($this);
    }
}
