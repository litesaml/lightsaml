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
    /**
     * @return string
     */
    protected function getProfileId()
    {
        return Profiles::SSO_SP_RECEIVE_RESPONSE;
    }

    /**
     * @return string
     */
    protected function getProfileRole()
    {
        return ProfileContext::ROLE_SP;
    }

    /**
     * @return ActionBuilderInterface
     */
    protected function getActionBuilder()
    {
        return new SsoSpReceiveResponseActionBuilder(
            $this->container,
            new SsoSpValidateAssertionActionBuilder($this->container)
        );
    }
}
