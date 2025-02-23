<?php

namespace LightSaml\Action;

use LightSaml\Context\ContextInterface;
use Psr\Log\LoggerInterface;

class LoggableAction extends WrappedAction
{
    public function __construct(ActionInterface $action, private readonly LoggerInterface $logger)
    {
        parent::__construct($action);
    }

    protected function beforeAction(ContextInterface $context)
    {
        $this->logger->debug(sprintf('Executing action "%s"', $this->action::class), [
            'context' => $context,
            'action' => $this->action,
        ]);
    }

    protected function afterAction(ContextInterface $context)
    {
    }
}
