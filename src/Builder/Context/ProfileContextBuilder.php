<?php

namespace LightSaml\Builder\Context;

use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Error\LightSamlBuildException;
use LightSaml\Provider\EntityDescriptor\EntityDescriptorProviderInterface;
use Psr\Http\Message\ServerRequestInterface;

class ProfileContextBuilder
{
    private ?ServerRequestInterface $request = null;

    private ?EntityDescriptorProviderInterface $ownEntityDescriptorProvider = null;

    private ?string $profileId = null;

    private ?string $profileRole = null;

    /**
     */
    public function getRequest(): ?ServerRequestInterface
    {
        return $this->request;
    }

    public function setRequest(ServerRequestInterface $request): static
    {
        $this->request = $request;

        return $this;
    }

    /**
     */
    public function getOwnEntityDescriptorProvider(): ?EntityDescriptorProviderInterface
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

    public function build(): ProfileContext
    {
        if (!$this->request instanceof ServerRequestInterface) {
            throw new LightSamlBuildException('HTTP Request not set');
        }
        if (!$this->ownEntityDescriptorProvider instanceof EntityDescriptorProviderInterface) {
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
