<?php

namespace LightSaml\Builder\Profile\WebBrowserSso\Sp;

use LightSaml\Builder\Action\ActionBuilderInterface;
use LightSaml\Builder\Action\Profile\SingleSignOn\Sp\SsoSpReceiveResponseActionBuilder;
use LightSaml\Builder\Action\Profile\SingleSignOn\Sp\SsoSpValidateAssertionActionBuilder;
use LightSaml\Builder\Profile\AbstractProfileBuilder;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Profile\Profiles;

class SsoSpReceiveResponseProfileBuilder extends AbstractProfileBuilder
{
    protected function getProfileId(): string
    {
        return Profiles::SSO_SP_RECEIVE_RESPONSE;
    }

    protected function getProfileRole(): string
    {
        return ProfileContext::ROLE_SP;
    }

    protected function getActionBuilder(): SsoSpReceiveResponseActionBuilder
    {
        return new SsoSpReceiveResponseActionBuilder(
            $this->container,
            new SsoSpValidateAssertionActionBuilder($this->container)
        );
    }
}
