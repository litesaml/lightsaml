<?php

namespace LightSaml\Action\Profile\Outbound\Message;

use LightSaml\Action\Profile\AbstractProfileAction;
use LightSaml\Context\Profile\Helper\LogHelper;
use LightSaml\Context\Profile\Helper\MessageContextHelper;
use LightSaml\Context\Profile\ProfileContext;
use Psr\Log\LoggerInterface;

/**
 * Sets the Version of the outbound message to the given value.
 */
class MessageVersionAction extends AbstractProfileAction
{
    public function __construct(LoggerInterface $logger, private readonly string $version)
    {
        parent::__construct($logger);
    }

    protected function doExecute(ProfileContext $context): void
    {
        MessageContextHelper::asSamlMessage($context->getOutboundContext())
            ->setVersion($this->version);

        $this->logger->debug(
            sprintf('Message Version set to "%s"', $this->version),
            LogHelper::getActionContext($context, $this)
        );
    }
}
