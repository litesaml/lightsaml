<?php

namespace Tests\Mock\Action;

use LightSaml\Action\ActionInterface;
use LightSaml\Context\ContextInterface;

class BarAction implements ActionInterface
{
    public function execute(ContextInterface $context): void
    {
        // bar
    }
}
