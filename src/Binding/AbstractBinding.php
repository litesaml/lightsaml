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

    /**
     * @return AbstractBinding
     */
    public function setEventDispatcher(?EventDispatcherInterface $eventDispatcher = null)
    {
        $this->eventDispatcher = $eventDispatcher;

        return $this;
    }

    /**
     * @return EventDispatcherInterface|null
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @param string $messageString
     */
    protected function dispatchReceive($messageString)
    {
        if ($this->eventDispatcher) {
            $this->eventDispatcher->dispatch(new MessageReceived($messageString));
        }
    }

    /**
     * @param string $messageString
     */
    protected function dispatchSend($messageString)
    {
        if ($this->eventDispatcher) {
            $this->eventDispatcher->dispatch(new MessageSent($messageString));
        }
    }

    /**
     * @param string|null $destination
     *
     * @return ResponseInterface
     */
    abstract public function send(MessageContext $context, $destination = null);

    abstract public function receive(ServerRequestInterface $request, MessageContext $context): SamlMessage;
}
