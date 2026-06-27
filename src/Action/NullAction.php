<?php

namespace LightSaml\Action;

use LightSaml\Context\ContextInterface;

class NullAction implements ActionInterface
{
    public function execute(ContextInterface $context): void
    {
        // null
    }
}
