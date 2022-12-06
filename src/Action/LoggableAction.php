<?php

namespace LightSaml\Action;

use LightSaml\Context\ContextInterface;
use Psr\Log\LoggerInterface;

class LoggableAction extends WrappedAction
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(ActionInterface $action, LoggerInterface $logger)
    {
        parent::__construct($action);

        $this->logger = $logger;
    }

    protected function beforeAction(ContextInterface $context)
    {
        $this->logger->debug(sprintf('Executing action "%s"', get_class($this->action)), [
            'context' => $context,
            'action' => $this->action,
        ]);
    }

    protected function afterAction(ContextInterface $context)
    {
    }
}
