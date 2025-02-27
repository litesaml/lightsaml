<?php

namespace LightSaml\Bridge\Pimple\Container\Factory;

use InvalidArgumentException;
use LightSaml\Bridge\Pimple\Container\CredentialContainer;
use LightSaml\Build\Container\OwnContainerInterface;
use LightSaml\Build\Container\PartyContainerInterface;
use LightSaml\Credential\CredentialInterface;
use LightSaml\Error\LightSamlBuildException;
use LightSaml\Store\Credential\Factory\CredentialFactory;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * @deprecated 5.0.0 No longer used by internal code and not recommended
 */
class CredentialContainerProvider implements ServiceProviderInterface
{
    /** @var CredentialInterface[] */
    private $extraCredentials = [];

    public function __construct(private readonly PartyContainerInterface $partyContainer, private readonly OwnContainerInterface $ownContainer)
    {
    }

    /**
     * @return CredentialContainerProvider
     */
    public function addExtraCredential(CredentialInterface $credential)
    {
        if (null === $credential->getEntityId()) {
            throw new InvalidArgumentException('Extra credential must have entityID');
        }

        $this->extraCredentials[] = $credential;

        return $this;
    }

    /**
     * @param Container $pimple A container instance
     */
    public function register(Container $pimple)
    {
        $ownCredentials = $this->ownContainer->getOwnCredentials();
        if (empty($ownCredentials)) {
            throw new LightSamlBuildException('There are no own credentials');
        }

        $pimple[CredentialContainer::CREDENTIAL_STORE] = function () {
            $factory = new CredentialFactory();

            return $factory->build(
                $this->partyContainer->getIdpEntityDescriptorStore(),
                $this->partyContainer->getSpEntityDescriptorStore(),
                $this->ownContainer->getOwnCredentials(),
                $this->extraCredentials
            );
        };
    }
}
