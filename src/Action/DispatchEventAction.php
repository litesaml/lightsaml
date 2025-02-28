<?php

namespace LightSaml\Action;

use LightSaml\Context\ContextInterface;
use LightSaml\Event\ActionOccurred;
use Psr\EventDispatcher\EventDispatcherInterface;

class DispatchEventAction implements ActionInterface
{
    public function __construct(protected EventDispatcherInterface $eventDispatcher)
    {
    }

    /**
     * @return void
     */
    public function execute(ContextInterface $context)
    {
        $this->eventDispatcher->dispatch(new ActionOccurred($context));
    }
}
