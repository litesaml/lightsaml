<?php

namespace LightSaml\Action\Profile\Inbound\Response;

use LightSaml\Action\Profile\AbstractProfileAction;
use LightSaml\Context\Profile\Helper\MessageContextHelper;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Resolver\Session\SessionProcessorInterface;
use Psr\Log\LoggerInterface;

class SpSsoStateAction extends AbstractProfileAction
{
    public function __construct(LoggerInterface $logger, private readonly SessionProcessorInterface $sessionProcessor)
    {
        parent::__construct($logger);
    }

    protected function doExecute(ProfileContext $context)
    {
        $response = MessageContextHelper::asResponse($context->getInboundContext());

        $this->sessionProcessor->processAssertions(
            $response->getAllAssertions(),
            $context->getOwnEntityDescriptor()->getEntityID(),
            $context->getPartyEntityDescriptor()->getEntityID()
        );
    }
}
