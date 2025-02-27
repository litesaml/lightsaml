<?php

namespace LightSaml\Action\Profile\Inbound\Message;

use LightSaml\Action\Profile\AbstractProfileAction;
use LightSaml\Context\Profile\Helper\LogHelper;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Error\LightSamlContextException;
use Psr\Log\LoggerInterface;

class AssertBindingTypeAction extends AbstractProfileAction
{
    /**
     * @param string[] $expectedBindingTypes
     */
    public function __construct(LoggerInterface $logger, protected array $expectedBindingTypes)
    {
        parent::__construct($logger);
    }

    protected function doExecute(ProfileContext $context)
    {
        if (false === in_array($context->getInboundContext()->getBindingType(), $this->expectedBindingTypes, true)) {
            $message = sprintf(
                'Unexpected binding type "%s" - expected binding types are: %s',
                $context->getInboundContext()->getBindingType(),
                implode(' ', $this->expectedBindingTypes)
            );
            $this->logger->critical($message, LogHelper::getActionErrorContext($context, $this, [
                'actualBindingType' => $context->getInboundContext()->getBindingType(),
                'expectedBindingTypes' => $this->expectedBindingTypes,
            ]));

            throw new LightSamlContextException($context, $message);
        }
    }
}
