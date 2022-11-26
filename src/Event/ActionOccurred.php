<?php

namespace LightSaml\Event;

use LightSaml\Context\ContextInterface;

class ActionOccurred
{
    public ContextInterface $context;

    public function __construct(ContextInterface $context)
    {
        $this->context = $context;
    }
}
