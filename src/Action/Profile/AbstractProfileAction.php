<?php

namespace LightSaml\Action\Profile;

use LightSaml\Action\ActionInterface;
use LightSaml\Context\ContextInterface;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Error\LightSamlContextException;
use Psr\Log\LoggerInterface;

abstract class AbstractProfileAction implements ActionInterface
{
    public function __construct(protected LoggerInterface $logger)
    {
    }

    public function execute(ContextInterface $context): void
    {
        if ($context instanceof ProfileContext) {
            $this->doExecute($context);
        } else {
            $message = sprintf('Expected ProfileContext but got %s', $context::class);
            $this->logger->emergency($message, ['context' => $context]);
            throw new LightSamlContextException($context, $message);
        }
    }

    abstract protected function doExecute(ProfileContext $context);
}
