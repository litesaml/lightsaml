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
    public function getRequest(): ServerRequestInterface;

    public function getResponseFactory(): ResponseFactoryInterface;

    public function getStreamFactory(): StreamFactoryInterface;

    public function getTimeProvider(): TimeProviderInterface;

    public function getEventDispatcher(): EventDispatcherInterface;

    public function getLogger(): LoggerInterface;
}
