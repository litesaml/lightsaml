<?php

namespace LightSaml\Builder\Context;

use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Error\LightSamlBuildException;
use LightSaml\Provider\EntityDescriptor\EntityDescriptorProviderInterface;
use Psr\Http\Message\ServerRequestInterface;

class ProfileContextBuilder
{
    private ?\Psr\Http\Message\ServerRequestInterface $request = null;

    private ?\LightSaml\Provider\EntityDescriptor\EntityDescriptorProviderInterface $ownEntityDescriptorProvider = null;

    private ?string $profileId = null;

    private ?string $profileRole = null;

    /**
     * @return ServerRequestInterface
     */
    public function getRequest(): ?\Psr\Http\Message\ServerRequestInterface
    {
        return $this->request;
    }

    public function setRequest(ServerRequestInterface $request): static
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return EntityDescriptorProviderInterface
     */
    public function getOwnEntityDescriptorProvider(): ?\LightSaml\Provider\EntityDescriptor\EntityDescriptorProviderInterface
    {
        return $this->ownEntityDescriptorProvider;
    }

    public function setOwnEntityDescriptorProvider(EntityDescriptorProviderInterface $ownEntityDescriptorProvider): static
    {
        $this->ownEntityDescriptorProvider = $ownEntityDescriptorProvider;

        return $this;
    }

    public function getProfileId(): ?string
    {
        return $this->profileId;
    }

    public function setProfileId(string $profileId): static
    {
        $this->profileId = $profileId;

        return $this;
    }

    public function getProfileRole(): ?string
    {
        return $this->profileRole;
    }

    public function setProfileRole(string $profileRole): static
    {
        $this->profileRole = $profileRole;

        return $this;
    }

    public function build(): \LightSaml\Context\Profile\ProfileContext
    {
        if (!$this->request instanceof \Psr\Http\Message\ServerRequestInterface) {
            throw new LightSamlBuildException('HTTP Request not set');
        }
        if (!$this->ownEntityDescriptorProvider instanceof \LightSaml\Provider\EntityDescriptor\EntityDescriptorProviderInterface) {
            throw new LightSamlBuildException('Own EntityDescriptor not set');
        }
        if (null === $this->profileId) {
            throw new LightSamlBuildException('ProfileID not set');
        }
        if (null === $this->profileRole) {
            throw new LightSamlBuildException('Profile role not set');
        }

        $result = new ProfileContext($this->profileId, $this->profileRole);

        $result->getHttpRequestContext()->setRequest($this->request);
        $result->getOwnEntityContext()->setEntityDescriptor($this->ownEntityDescriptorProvider->get());

        return $result;
    }
}
