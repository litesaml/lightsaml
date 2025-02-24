<?php

namespace Tests\Mock\Action;

use LightSaml\Action\ActionInterface;
use LightSaml\Context\ContextInterface;

class BarAction implements ActionInterface
{
    /**
     * @return void
     */
    public function execute(ContextInterface $context)
    {
        // bar
    }
}
