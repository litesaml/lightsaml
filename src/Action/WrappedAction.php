<?php

namespace LightSaml\Action;

use LightSaml\Context\ContextInterface;

abstract class WrappedAction implements ActionInterface
{
    public function __construct(protected ActionInterface $action)
    {
    }

    public function execute(ContextInterface $context): void
    {
        $this->beforeAction($context);
        $this->action->execute($context);
        $this->afterAction($context);
    }

    abstract protected function beforeAction(ContextInterface $context): void;

    abstract protected function afterAction(ContextInterface $context): void;
}
