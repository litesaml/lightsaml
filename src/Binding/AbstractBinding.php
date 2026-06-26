<?php

namespace LightSaml\Binding;

use LightSaml\Context\Profile\MessageContext;
use LightSaml\Event\MessageReceived;
use LightSaml\Event\MessageSent;
use LightSaml\Model\Protocol\SamlMessage;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractBinding
{
    /** @var EventDispatcherInterface|null */
    protected $eventDispatcher;

    public function setEventDispatcher(?EventDispatcherInterface $eventDispatcher = null): \LightSaml\Binding\AbstractBinding
    {
        $this->eventDispatcher = $eventDispatcher;

        return $this;
    }

    public function getEventDispatcher(): ?\Psr\EventDispatcher\EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }

    protected function dispatchReceive(string $messageString)
    {
        if ($this->eventDispatcher) {
            $this->eventDispatcher->dispatch(new MessageReceived($messageString));
        }
    }

    protected function dispatchSend(string $messageString)
    {
        if ($this->eventDispatcher) {
            $this->eventDispatcher->dispatch(new MessageSent($messageString));
        }
    }

    
    abstract public function send(MessageContext $context, ?string $destination = null): \Psr\Http\Message\ResponseInterface;

    abstract public function receive(ServerRequestInterface $request, MessageContext $context): SamlMessage;
}
