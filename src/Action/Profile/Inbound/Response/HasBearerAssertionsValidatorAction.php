<?php

namespace LightSaml\Action\Profile\Inbound\Response;

use LightSaml\Action\Profile\AbstractProfileAction;
use LightSaml\Context\Profile\Helper\LogHelper;
use LightSaml\Context\Profile\Helper\MessageContextHelper;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Error\LightSamlContextException;

class HasBearerAssertionsValidatorAction extends AbstractProfileAction
{
    protected function doExecute(ProfileContext $context)
    {
        $response = MessageContextHelper::asResponse($context->getInboundContext());

        if ($response->getBearerAssertions()) {
            return;
        }

        $message = 'Response must contain at least one bearer assertion';
        $this->logger->error($message, LogHelper::getActionErrorContext($context, $this));
        throw new LightSamlContextException($context, $message);
    }
}
