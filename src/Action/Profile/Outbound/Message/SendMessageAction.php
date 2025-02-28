<?php

namespace LightSaml\Action\Profile\Outbound\Message;

use LightSaml\Action\Profile\AbstractProfileAction;
use LightSaml\Binding\BindingFactoryInterface;
use LightSaml\Context\Profile\Helper\LogHelper;
use LightSaml\Context\Profile\ProfileContext;
use Psr\Log\LoggerInterface;

class SendMessageAction extends AbstractProfileAction
{
    public function __construct(LoggerInterface $logger, protected BindingFactoryInterface $bindingFactory)
    {
        parent::__construct($logger);
    }

    /**
     * @return void
     */
    public function doExecute(ProfileContext $context)
    {
        $binding = $this->bindingFactory->create($context->getEndpoint()->getBinding());

        $outboundContext = $context->getOutboundContext();

        $context->getHttpResponseContext()->setResponse(
            $binding->send($outboundContext)
        );

        $this->logger->info(
            'Sending message',
            LogHelper::getActionContext($context, $this, [
                'message' => $outboundContext->getSerializationContext()->getDocument()->saveXML(),
            ])
        );
    }
}
