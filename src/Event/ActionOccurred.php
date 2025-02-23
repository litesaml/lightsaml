<?php

namespace LightSaml\Event;

use LightSaml\Context\ContextInterface;

class ActionOccurred
{
    public function __construct(public ContextInterface $context)
    {
    }
}
