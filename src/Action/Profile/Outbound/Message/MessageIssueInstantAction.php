<?php

namespace LightSaml\Action\Profile\Outbound\Message;

use LightSaml\Action\Profile\AbstractProfileAction;
use LightSaml\Context\Profile\Helper\LogHelper;
use LightSaml\Context\Profile\Helper\MessageContextHelper;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Provider\TimeProvider\TimeProviderInterface;
use Psr\Log\LoggerInterface;

/**
 * Sets outbound message IssueInstant to the value provided by given time provider.
 */
class MessageIssueInstantAction extends AbstractProfileAction
{
    public function __construct(LoggerInterface $logger, protected TimeProviderInterface $timeProvider)
    {
        parent::__construct($logger);
    }

    /**
     * @return void
     */
    protected function doExecute(ProfileContext $context)
    {
        MessageContextHelper::asSamlMessage($context->getOutboundContext())
            ->setIssueInstant($this->timeProvider->getTimestamp());

        $this->logger->info(
            sprintf('Message IssueInstant set to "%s"', MessageContextHelper::asSamlMessage($context->getOutboundContext())->getIssueInstantString()),
            LogHelper::getActionContext($context, $this)
        );
    }
}
