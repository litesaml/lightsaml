<?php

namespace LightSaml\Resolver\Credential;

abstract class AbstractCompositeResolver extends AbstractQueryableResolver
{
    /** @var CredentialResolverInterface[] */
    protected array $resolvers = [];

    public function add(CredentialResolverInterface $resolver): AbstractCompositeResolver
    {
        $this->resolvers[] = $resolver;

        return $this;
    }
}
