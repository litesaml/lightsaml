<?php

namespace LightSaml\Action;

use LightSaml\Context\ContextInterface;
use LightSaml\Event\ActionOccurred;
use Psr\EventDispatcher\EventDispatcherInterface;

class DispatchEventAction implements ActionInterface
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return void
     */
    public function execute(ContextInterface $context)
    {
        $this->eventDispatcher->dispatch(new ActionOccurred($context));
    }
}
