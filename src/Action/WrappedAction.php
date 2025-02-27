<?php

namespace LightSaml\Action;

use LightSaml\Context\ContextInterface;

abstract class WrappedAction implements ActionInterface
{
    public function __construct(protected ActionInterface $action)
    {
    }

    /**
     * @return void
     */
    public function execute(ContextInterface $context)
    {
        $this->beforeAction($context);
        $this->action->execute($context);
        $this->afterAction($context);
    }

    abstract protected function beforeAction(ContextInterface $context);

    abstract protected function afterAction(ContextInterface $context);
}
