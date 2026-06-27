<?php

namespace LightSaml\Context\Profile;

use LightSaml\Error\LightSamlContextException;
use LightSaml\Meta\TrustOptions\TrustOptions;
use LightSaml\Model\Metadata\Endpoint;
use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\Model\Protocol\SamlMessage;
use LightSaml\State\Sso\SsoSessionState;
use Psr\Http\Message\ServerRequestInterface;

class ProfileContext extends AbstractProfileContext
{
    public const ROLE_SP = 'sp';
    public const ROLE_IDP = 'idp';
    public const ROLE_NONE = 'none';

    private ?string $relayState = null;

    public function __construct(private readonly string $profileId, private readonly string $ownRole)
    {
    }

    public function getProfileId(): string
    {
        return $this->profileId;
    }

    public function getOwnRole(): string
    {
        return $this->ownRole;
    }

    public function getRelayState(): string
    {
        return $this->relayState;
    }

    public function setRelayState(string $relayState): static
    {
        $this->relayState = $relayState;

        return $this;
    }

    public function getInboundContext(): MessageContext
    {
        return $this->getSubContext(ProfileContexts::INBOUND_MESSAGE, MessageContext::class);
    }

    public function getOutboundContext(): MessageContext
    {
        return $this->getSubContext(ProfileContexts::OUTBOUND_MESSAGE, MessageContext::class);
    }

    public function getHttpRequestContext(): HttpRequestContext
    {
        return $this->getSubContext(ProfileContexts::HTTP_REQUEST, HttpRequestContext::class);
    }

    public function getHttpResponseContext(): HttpResponseContext
    {
        return $this->getSubContext(ProfileContexts::HTTP_RESPONSE, HttpResponseContext::class);
    }

    public function getOwnEntityContext(): EntityContext
    {
        return $this->getSubContext(ProfileContexts::OWN_ENTITY, EntityContext::class);
    }

    public function getPartyEntityContext(): EntityContext
    {
        return $this->getSubContext(ProfileContexts::PARTY_ENTITY, EntityContext::class);
    }

    public function getEndpointContext(): EndpointContext
    {
        return $this->getSubContext(ProfileContexts::ENDPOINT, EndpointContext::class);
    }

    public function getLogoutContext(): LogoutContext
    {
        return $this->getSubContext(ProfileContexts::LOGOUT, LogoutContext::class);
    }

    public function getHttpRequest(): ServerRequestInterface
    {
        $httpRequestContext = $this->getHttpRequestContext();
        if (!$httpRequestContext->getRequest() instanceof ServerRequestInterface) {
            throw new LightSamlContextException($this, 'Missing Request in HTTP request context');
        }

        return $httpRequestContext->getRequest();
    }

    public function getInboundMessage(): SamlMessage
    {
        $inboundContext = $this->getInboundContext();
        if (!$inboundContext->getMessage() instanceof SamlMessage) {
            throw new LightSamlContextException($this, 'Missing message in inbound context');
        }

        return $inboundContext->getMessage();
    }

    public function getOutboundMessage(): SamlMessage
    {
        $outboundContext = $this->getOutboundContext();
        if (!$outboundContext->getMessage() instanceof SamlMessage) {
            throw new LightSamlContextException($this, 'Missing message in outbound context');
        }

        return $outboundContext->getMessage();
    }

    public function getEndpoint(): Endpoint
    {
        $endpointContext = $this->getEndpointContext();
        if (!$endpointContext->getEndpoint() instanceof Endpoint) {
            throw new LightSamlContextException($this, 'Missing Endpoint in endpoint context');
        }

        return $endpointContext->getEndpoint();
    }

    public function getOwnEntityDescriptor(): EntityDescriptor
    {
        $ownEntityContext = $this->getOwnEntityContext();
        if (!$ownEntityContext->getEntityDescriptor() instanceof EntityDescriptor) {
            throw new LightSamlContextException($this, 'Missing EntityDescriptor in own entity context');
        }

        return $ownEntityContext->getEntityDescriptor();
    }

    public function getPartyEntityDescriptor(): EntityDescriptor
    {
        $partyEntityContext = $this->getPartyEntityContext();
        if (!$partyEntityContext->getEntityDescriptor() instanceof EntityDescriptor) {
            throw new LightSamlContextException($this, 'Missing EntityDescriptor in party entity context');
        }

        return $partyEntityContext->getEntityDescriptor();
    }

    public function getTrustOptions(): TrustOptions
    {
        $partyEntityContext = $this->getPartyEntityContext();
        if (!$partyEntityContext->getTrustOptions() instanceof TrustOptions) {
            throw new LightSamlContextException($this, 'Missing TrustOptions in party entity context');
        }

        return $partyEntityContext->getTrustOptions();
    }

    public function getLogoutSsoSessionState(): ?SsoSessionState
    {
        $logoutContext = $this->getLogoutContext();
        if (null == $logoutContext->getSsoSessionState()) {
            throw new LightSamlContextException($this, 'Missing SsoSessionState in logout context');
        }

        return $logoutContext->getSsoSessionState();
    }
}
