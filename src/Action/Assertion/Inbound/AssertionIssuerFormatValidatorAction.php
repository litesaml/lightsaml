<?php

namespace LightSaml\Action\Assertion\Inbound;

use LightSaml\Action\Assertion\AbstractAssertionAction;
use LightSaml\Context\Profile\AssertionContext;
use LightSaml\Context\Profile\Helper\LogHelper;
use LightSaml\Error\LightSamlContextException;
use Psr\Log\LoggerInterface;

class AssertionIssuerFormatValidatorAction extends AbstractAssertionAction
{
    /**
     * @param string $expectedIssuerFormat
     */
    public function __construct(LoggerInterface $logger, private $expectedIssuerFormat)
    {
        parent::__construct($logger);
    }

    protected function doExecute(AssertionContext $context)
    {
        if (null == $context->getAssertion()->getIssuer()) {
            $message = 'Assertion element must have an issuer element';
            $this->logger->error($message, LogHelper::getActionErrorContext($context, $this));
            throw new LightSamlContextException($context, $message);
        }

        if (
            $context->getAssertion()->getIssuer()->getFormat()
            && $context->getAssertion()->getIssuer()->getFormat() != $this->expectedIssuerFormat
        ) {
            $message = sprintf(
                "Response Issuer Format if set must have value '%s' but it was '%s'",
                $this->expectedIssuerFormat,
                $context->getAssertion()->getIssuer()->getFormat()
            );
            $this->logger->error($message, LogHelper::getActionErrorContext($context, $this, [
                'actualFormat' => $context->getAssertion()->getIssuer()->getFormat(),
                'expectedFormat' => $this->expectedIssuerFormat,
            ]));
            throw new LightSamlContextException($context, $message);
        }
    }
}
