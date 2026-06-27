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
    protected ?EventDispatcherInterface $eventDispatcher = null;

    public function setEventDispatcher(?EventDispatcherInterface $eventDispatcher = null): AbstractBinding
    {
        $this->eventDispatcher = $eventDispatcher;

        return $this;
    }

    public function getEventDispatcher(): ?EventDispatcherInterface
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

    abstract public function send(MessageContext $context, ?string $destination = null): ResponseInterface;

    abstract public function receive(ServerRequestInterface $request, MessageContext $context): SamlMessage;
}
