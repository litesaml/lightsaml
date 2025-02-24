<?php

namespace LightSaml\Bridge\Pimple\Container\Factory;

use LightSaml\Binding\BindingFactory;
use LightSaml\Bridge\Pimple\Container\ServiceContainer;
use LightSaml\Build\Container\CredentialContainerInterface;
use LightSaml\Build\Container\StoreContainerInterface;
use LightSaml\Build\Container\SystemContainerInterface;
use LightSaml\Resolver\Credential\Factory\CredentialResolverFactory;
use LightSaml\Resolver\Endpoint\BindingEndpointResolver;
use LightSaml\Resolver\Endpoint\CompositeEndpointResolver;
use LightSaml\Resolver\Endpoint\DescriptorTypeEndpointResolver;
use LightSaml\Resolver\Endpoint\IndexEndpointResolver;
use LightSaml\Resolver\Endpoint\LocationEndpointResolver;
use LightSaml\Resolver\Endpoint\ServiceTypeEndpointResolver;
use LightSaml\Resolver\Session\SessionProcessor;
use LightSaml\Resolver\Signature\OwnSignatureResolver;
use LightSaml\Validator\Model\Assertion\AssertionTimeValidator;
use LightSaml\Validator\Model\Assertion\AssertionValidator;
use LightSaml\Validator\Model\NameId\NameIdValidator;
use LightSaml\Validator\Model\Signature\SignatureValidator;
use LightSaml\Validator\Model\Statement\StatementValidator;
use LightSaml\Validator\Model\Subject\SubjectValidator;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * @deprecated 5.0.0 No longer used by internal code and not recommended
 */
class ServiceContainerProvider implements ServiceProviderInterface
{
    public function __construct(private readonly CredentialContainerInterface $credentialContainer, private readonly StoreContainerInterface $storeContainer, private readonly SystemContainerInterface $systemContainer)
    {
    }

    /**
     * @param Container $pimple A container instance
     */
    public function register(Container $pimple)
    {
        $pimple[ServiceContainer::NAME_ID_VALIDATOR] = function () {
            return new NameIdValidator();
        };

        $pimple[ServiceContainer::ASSERTION_TIME_VALIDATOR] = function () {
            return new AssertionTimeValidator();
        };

        $pimple[ServiceContainer::ASSERTION_VALIDATOR] = function (Container $c) {
            $nameIdValidator = $c[ServiceContainer::NAME_ID_VALIDATOR];

            return new AssertionValidator(
                $nameIdValidator,
                new SubjectValidator($nameIdValidator),
                new StatementValidator()
            );
        };

        $pimple[ServiceContainer::ENDPOINT_RESOLVER] = function () {
            return new CompositeEndpointResolver([
                new BindingEndpointResolver(),
                new DescriptorTypeEndpointResolver(),
                new ServiceTypeEndpointResolver(),
                new IndexEndpointResolver(),
                new LocationEndpointResolver(),
            ]);
        };

        $pimple[ServiceContainer::BINDING_FACTORY] = function () {
            return new BindingFactory($this->systemContainer->getEventDispatcher());
        };

        $pimple[ServiceContainer::CREDENTIAL_RESOLVER] = function () {
            $factory = new CredentialResolverFactory($this->credentialContainer->getCredentialStore());

            return $factory->build();
        };

        $pimple[ServiceContainer::SIGNATURE_RESOLVER] = function (Container $c) {
            $credentialResolver = $c[ServiceContainer::CREDENTIAL_RESOLVER];

            return new OwnSignatureResolver($credentialResolver);
        };

        $pimple[ServiceContainer::SIGNATURE_VALIDATOR] = function (Container $c) {
            $credentialResolver = $c[ServiceContainer::CREDENTIAL_RESOLVER];

            return new SignatureValidator($credentialResolver);
        };

        $pimple[ServiceContainer::SESSION_PROCESSOR] = function () {
            return new SessionProcessor($this->storeContainer->getSsoStateStore(), $this->systemContainer->getTimeProvider());
        };
    }
}
