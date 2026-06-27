<?php

namespace LightSaml\Action\Profile\Outbound\Message;

use LightSaml\Action\Profile\AbstractProfileAction;
use LightSaml\Context\Profile\Helper\LogHelper;
use LightSaml\Context\Profile\Helper\MessageContextHelper;
use LightSaml\Context\Profile\ProfileContext;

class SetRelayStateAction extends AbstractProfileAction
{
    protected function doExecute(ProfileContext $context): void
    {
        $relayState = $context->getRelayState();
        if ($relayState !== '') {
            $this->logger->debug(
                sprintf('RelayState from context set to outbound message: "%s"', $relayState),
                LogHelper::getActionContext($context, $this)
            );
            MessageContextHelper::asSamlMessage($context->getOutboundContext())
                ->setRelayState($relayState);
        }
    }
}
