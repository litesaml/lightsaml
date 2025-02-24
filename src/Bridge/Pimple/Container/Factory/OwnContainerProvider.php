<?php

namespace LightSaml\Bridge\Pimple\Container\Factory;

use LightSaml\Bridge\Pimple\Container\OwnContainer;
use LightSaml\Credential\CredentialInterface;
use LightSaml\Error\LightSamlBuildException;
use LightSaml\Provider\EntityDescriptor\EntityDescriptorProviderInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * @deprecated 5.0.0 No longer used by internal code and not recommended
 */
class OwnContainerProvider implements ServiceProviderInterface
{
    /** @var CredentialInterface[] */
    private $ownCredentials = [];

    /**
     * @param CredentialInterface[] $ownCredentials
     */
    public function __construct(private readonly EntityDescriptorProviderInterface $ownEntityDescriptorProvider, ?array $ownCredentials = null)
    {
        if ($ownCredentials) {
            foreach ($ownCredentials as $credential) {
                $this->addOwnCredential($credential);
            }
        }
    }

    /**
     * @return OwnContainerProvider
     */
    public function addOwnCredential(CredentialInterface $credential)
    {
        if (null == $credential->getPrivateKey()) {
            throw new LightSamlBuildException('Own credential must have private key');
        }

        $this->ownCredentials[] = $credential;

        return $this;
    }

    /**
     * @param Container $pimple A container instance
     */
    public function register(Container $pimple)
    {
        $pimple[OwnContainer::OWN_CREDENTIALS] = function () {
            return $this->ownCredentials;
        };

        $pimple[OwnContainer::OWN_ENTITY_DESCRIPTOR_PROVIDER] = function () {
            return $this->ownEntityDescriptorProvider;
        };
    }
}
