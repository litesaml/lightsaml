<?php

namespace LightSaml\Resolver\Credential;

abstract class AbstractCompositeResolver extends AbstractQueryableResolver
{
    /** @var CredentialResolverInterface[] */
    protected $resolvers = [];

    /**
     * @return AbstractCompositeResolver
     */
    public function add(CredentialResolverInterface $resolver)
    {
        $this->resolvers[] = $resolver;

        return $this;
    }
}
