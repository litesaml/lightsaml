<?php

namespace LightSaml\Bridge\Pimple\Container;

use LightSaml\Build\Container\SystemContainerInterface;
use LightSaml\Provider\TimeProvider\TimeProviderInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @deprecated 5.0.0 No longer used by internal code and not recommended
 */
class SystemContainer extends AbstractPimpleContainer implements SystemContainerInterface
{
    public const REQUEST = 'lightsaml.container.request';
    public const SESSION = 'lightsaml.container.session';
    public const TIME_PROVIDER = 'lightsaml.container.time_provider';
    public const EVENT_DISPATCHER = 'lightsaml.container.event_dispatcher';
    public const LOGGER = 'lightsaml.container.logger';

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->pimple[self::REQUEST];
    }

    /**
     * @return SessionInterface
     */
    public function getSession()
    {
        return $this->pimple[self::SESSION];
    }

    /**
     * @return TimeProviderInterface
     */
    public function getTimeProvider()
    {
        return $this->pimple[self::TIME_PROVIDER];
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->pimple[self::EVENT_DISPATCHER];
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->pimple[self::LOGGER];
    }
}
