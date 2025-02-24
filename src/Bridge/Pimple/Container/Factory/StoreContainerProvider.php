<?php

namespace LightSaml\Bridge\Pimple\Container\Factory;

use LightSaml\Bridge\Pimple\Container\StoreContainer;
use LightSaml\Build\Container\SystemContainerInterface;
use LightSaml\Store\Id\NullIdStore;
use LightSaml\Store\Request\RequestStateSessionStore;
use LightSaml\Store\Sso\SsoStateSessionStore;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * @deprecated 5.0.0 No longer used by internal code and not recommended
 */
class StoreContainerProvider implements ServiceProviderInterface
{
    public function __construct(private readonly SystemContainerInterface $systemContainer)
    {
    }

    /**
     * @param Container $pimple A container instance
     */
    public function register(Container $pimple)
    {
        $pimple[StoreContainer::REQUEST_STATE_STORE] = function () {
            return new RequestStateSessionStore($this->systemContainer->getSession(), 'main');
        };

        $pimple[StoreContainer::ID_STATE_STORE] = function () {
            return new NullIdStore();
        };

        $pimple[StoreContainer::SSO_STATE_STORE] = function () {
            return new SsoStateSessionStore($this->systemContainer->getSession(), 'samlsso');
        };
    }
}
