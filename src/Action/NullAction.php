<?php

namespace LightSaml\Action;

use LightSaml\Context\ContextInterface;

class NullAction implements ActionInterface
{
    /**
     * @return void
     */
    public function execute(ContextInterface $context)
    {
        // null
    }
}
