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
    public function getRequest(): \Psr\Http\Message\ServerRequestInterface;

    public function getResponseFactory(): \Psr\Http\Message\ResponseFactoryInterface;

    public function getStreamFactory(): \Psr\Http\Message\StreamFactoryInterface;

    public function getTimeProvider(): \LightSaml\Provider\TimeProvider\TimeProviderInterface;

    public function getEventDispatcher(): \Psr\EventDispatcher\EventDispatcherInterface;

    public function getLogger(): \Psr\Log\LoggerInterface;
}
