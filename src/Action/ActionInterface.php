<?php

namespace LightSaml\Action;

use LightSaml\Context\ContextInterface;

interface ActionInterface
{
    public function execute(ContextInterface $context);
}
