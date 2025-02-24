<?php

namespace Tests\Mock\Action;

use LightSaml\Action\ActionInterface;
use LightSaml\Context\ContextInterface;

class FooAction implements ActionInterface
{
    /**
     * @return void
     */
    public function execute(ContextInterface $context)
    {
        // foo
    }
}
