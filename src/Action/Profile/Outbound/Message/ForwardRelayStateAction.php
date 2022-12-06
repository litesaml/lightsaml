<?php

namespace LightSaml\Action\Profile\Outbound\Message;

use LightSaml\Action\Profile\AbstractProfileAction;
use LightSaml\Context\Profile\ProfileContext;

class ForwardRelayStateAction extends AbstractProfileAction
{
    protected function doExecute(ProfileContext $context)
    {
        if (null == $context->getInboundContext()->getMessage()) {
            return;
        }

        if ($context->getInboundMessage()->getRelayState()) {
            $this->logger->debug(sprintf('Forwarding relay state from inbound message: "%s"', $context->getInboundMessage()->getRelayState()));
            $context->getOutboundMessage()->setRelayState(
                $context->getInboundMessage()->getRelayState()
            );
        }
    }
}
