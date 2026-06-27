<?php

namespace LightSaml\Action;

use Psr\Log\LoggerInterface;

class ActionLogWrapper implements ActionWrapperInterface
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function wrap(ActionInterface $action): LoggableAction
    {
        return new LoggableAction($action, $this->logger);
    }
}
