<?php

namespace LightSaml\Store\Credential\Factory;

use LightSaml\Credential\CredentialInterface;
use LightSaml\Error\LightSamlBuildException;
use LightSaml\Store\Credential\CompositeCredentialStore;
use LightSaml\Store\Credential\CredentialStoreInterface;
use LightSaml\Store\Credential\MetadataCredentialStore;
use LightSaml\Store\Credential\StaticCredentialStore;
use LightSaml\Store\EntityDescriptor\EntityDescriptorStoreInterface;

class CredentialFactory
{
    /** @var CredentialInterface[] */
    private array $extraCredentials = [];

    public function addExtraCredential(CredentialInterface $credential): static
    {
        $this->extraCredentials[] = $credential;

        return $this;
    }

    /**
     * @param CredentialInterface[] $extraCredentials
     *
     */
    public function buildFromOwnCredentialStore(
        EntityDescriptorStoreInterface $idpEntityDescriptorStore,
        EntityDescriptorStoreInterface $spEntityDescriptorStore,
        string $ownEntityId,
        CredentialStoreInterface $ownCredentialStore,
        ?array $extraCredentials = null
    ): CompositeCredentialStore {
        return $this->build(
            $idpEntityDescriptorStore,
            $spEntityDescriptorStore,
            $ownCredentialStore->getByEntityId($ownEntityId),
            $extraCredentials
        );
    }

    /**
     * @param CredentialInterface[] $ownCredentials
     * @param CredentialInterface[] $extraCredentials
     */
    public function build(
        EntityDescriptorStoreInterface $idpEntityDescriptorStore,
        EntityDescriptorStoreInterface $spEntityDescriptorStore,
        array $ownCredentials,
        ?array $extraCredentials = null
    ): CompositeCredentialStore {
        if ($ownCredentials === []) {
            throw new LightSamlBuildException('There are no own credentials');
        }

        $store = new CompositeCredentialStore();
        $store->add(new MetadataCredentialStore($idpEntityDescriptorStore));
        $store->add(new MetadataCredentialStore($spEntityDescriptorStore));

        $ownCredentialsStore = new StaticCredentialStore();
        foreach ($ownCredentials as $credential) {
            $ownCredentialsStore->add($credential);
        }
        $store->add($ownCredentialsStore);

        $extraCredentialsStore = new StaticCredentialStore();
        $store->add($extraCredentialsStore);
        foreach ($this->extraCredentials as $credential) {
            $extraCredentialsStore->add($credential);
        }
        if ($extraCredentials) {
            foreach ($extraCredentials as $credential) {
                $extraCredentialsStore->add($credential);
            }
        }

        return $store;
    }
}
