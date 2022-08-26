<?php

/*
 * This file is part of the LightSAML-Core package.
 *
 * (c) Milos Tomic <tmilos@lightsaml.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

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
