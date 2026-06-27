<?php

namespace LightSaml\Resolver\Credential;

abstract class AbstractCompositeResolver extends AbstractQueryableResolver
{
    /** @var CredentialResolverInterface[] */
    protected array $resolvers = [];

    public function add(CredentialResolverInterface $resolver): \LightSaml\Resolver\Credential\AbstractCompositeResolver
    {
        $this->resolvers[] = $resolver;

        return $this;
    }
}
