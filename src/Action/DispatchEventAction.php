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

    public function execute(ContextInterface $context): void
    {
        $this->eventDispatcher->dispatch(new ActionOccurred($context));
    }
}
