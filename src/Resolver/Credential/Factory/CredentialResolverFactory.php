<?php

namespace LightSaml\Resolver\Credential\Factory;

use LightSaml\Resolver\Credential\AlgorithmFilterResolver;
use LightSaml\Resolver\Credential\CompositeFilterResolver;
use LightSaml\Resolver\Credential\CredentialNameFilterResolver;
use LightSaml\Resolver\Credential\CredentialResolverInterface;
use LightSaml\Resolver\Credential\EntityIdResolver;
use LightSaml\Resolver\Credential\MetadataFilterResolver;
use LightSaml\Resolver\Credential\PrivateKeyResolver;
use LightSaml\Resolver\Credential\PublicKeyThumbprintResolver;
use LightSaml\Resolver\Credential\UsageFilterResolver;
use LightSaml\Resolver\Credential\X509CredentialResolver;
use LightSaml\Store\Credential\CredentialStoreInterface;

class CredentialResolverFactory
{
    public function __construct(protected CredentialStoreInterface $credentialStore)
    {
    }

    /**
     * @return CredentialResolverInterface
     */
    public function build()
    {
        return (new CompositeFilterResolver())
            ->add(new EntityIdResolver($this->credentialStore))
            ->add(new AlgorithmFilterResolver())
            ->add(new CredentialNameFilterResolver())
            ->add(new MetadataFilterResolver())
            ->add(new UsageFilterResolver())
            ->add(new PrivateKeyResolver())
            ->add(new X509CredentialResolver())
            ->add(new PublicKeyThumbprintResolver());
    }
}
