<?php

namespace LightSaml\Build\Container;

use LightSaml\Provider\TimeProvider\TimeProviderInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;

interface SystemContainerInterface
{
    /**
     * @return ServerRequestInterface
     */
    public function getRequest();

    /**
     * @return ResponseFactoryInterface
     */
    public function getResponseFactory();

    /**
     * @return StreamFactoryInterface
     */
    public function getStreamFactory();

    /**
     * @return TimeProviderInterface
     */
    public function getTimeProvider();

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher();

    /**
     * @return LoggerInterface
     */
    public function getLogger();
}
