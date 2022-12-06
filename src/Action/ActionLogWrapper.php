<?php

namespace LightSaml\Action;

use Psr\Log\LoggerInterface;

class ActionLogWrapper implements ActionWrapperInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return ActionInterface
     */
    public function wrap(ActionInterface $action)
    {
        return new LoggableAction($action, $this->logger);
    }
}
