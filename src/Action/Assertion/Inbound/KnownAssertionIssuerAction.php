<?php

namespace LightSaml\Action\Assertion\Inbound;

use LightSaml\Action\Assertion\AbstractAssertionAction;
use LightSaml\Context\Profile\AssertionContext;
use LightSaml\Context\Profile\Helper\LogHelper;
use LightSaml\Error\LightSamlContextException;
use LightSaml\Store\EntityDescriptor\EntityDescriptorStoreInterface;
use Psr\Log\LoggerInterface;

class KnownAssertionIssuerAction extends AbstractAssertionAction
{
    public function __construct(LoggerInterface $logger, private readonly EntityDescriptorStoreInterface $idpEntityDescriptorProvider)
    {
        parent::__construct($logger);
    }

    /**
     * @return void
     */
    protected function doExecute(AssertionContext $context)
    {
        if (null === $context->getAssertion()->getIssuer()) {
            $message = 'Assertion element must have an issuer element';
            $this->logger->error($message, LogHelper::getActionErrorContext($context, $this));
            throw new LightSamlContextException($context, $message);
        }

        if (false == $this->idpEntityDescriptorProvider->has($context->getAssertion()->getIssuer()->getValue())) {
            $message = sprintf("Unknown issuer '%s'", $context->getAssertion()->getIssuer()->getValue());
            $this->logger->error($message, LogHelper::getActionErrorContext($context, $this, [
                'messageIssuer' => $context->getAssertion()->getIssuer()->getValue(),
            ]));
            throw new LightSamlContextException($context, $message);
        }

        $this->logger->debug(
            sprintf('Known assertion issuer: "%s"', $context->getAssertion()->getIssuer()->getValue()),
            LogHelper::getActionContext($context, $this)
        );
    }
}
