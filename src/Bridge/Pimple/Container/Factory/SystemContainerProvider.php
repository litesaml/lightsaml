<?php

namespace LightSaml\Bridge\Pimple\Container\Factory;

use LightSaml\Bridge\Pimple\Container\SystemContainer;
use LightSaml\Provider\TimeProvider\SystemTimeProvider;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

/**
 * @deprecated 5.0.0 No longer used by internal code and not recommended
 */
class SystemContainerProvider implements ServiceProviderInterface
{
    /**
     * @param bool $mockSession
     */
    public function __construct(private $mockSession = false, private readonly ?EventDispatcherInterface $eventDispatcher = null)
    {
    }

    /**
     * @param Container $pimple A container instance
     */
    public function register(Container $pimple)
    {
        $pimple[SystemContainer::REQUEST] = function () {
            return Request::createFromGlobals();
        };

        $pimple[SystemContainer::SESSION] = function () {
            if ($this->mockSession) {
                $session = new Session(new MockArraySessionStorage());
            } else {
                $session = new Session();
            }
            $session->setName(sprintf('SID%s', mt_rand(1000, 9999)));
            $session->start();

            return $session;
        };

        $pimple[SystemContainer::TIME_PROVIDER] = function () {
            return new SystemTimeProvider();
        };

        $pimple[SystemContainer::EVENT_DISPATCHER] = function () {
            return $this->eventDispatcher;
        };

        $pimple[SystemContainer::LOGGER] = function () {
            return new NullLogger();
        };
    }
}
