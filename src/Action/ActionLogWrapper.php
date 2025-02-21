<?php

namespace LightSaml\Action;

use Psr\Log\LoggerInterface;

class ActionLogWrapper implements ActionWrapperInterface
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    /**
     * @return ActionInterface
     */
    public function wrap(ActionInterface $action)
    {
        return new LoggableAction($action, $this->logger);
    }
}
