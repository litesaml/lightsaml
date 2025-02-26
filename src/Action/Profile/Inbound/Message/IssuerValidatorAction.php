<?php

namespace LightSaml\Action\Profile\Inbound\Message;

use LightSaml\Action\Profile\AbstractProfileAction;
use LightSaml\Context\Profile\Helper\LogHelper;
use LightSaml\Context\Profile\Helper\MessageContextHelper;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Error\LightSamlContextException;
use LightSaml\Error\LightSamlValidationException;
use LightSaml\Validator\Model\NameId\NameIdValidatorInterface;
use Psr\Log\LoggerInterface;

class IssuerValidatorAction extends AbstractProfileAction
{
    /**
     * @param string $allowedFormat
     */
    public function __construct(LoggerInterface $logger, protected NameIdValidatorInterface $nameIdValidator, protected $allowedFormat)
    {
        parent::__construct($logger);
    }

    /**
     * @return void
     */
    protected function doExecute(ProfileContext $context)
    {
        $message = MessageContextHelper::asSamlMessage($context->getInboundContext());

        if (false == $message->getIssuer()) {
            $message = 'Inbound message must have Issuer element';
            $this->logger->emergency($message, LogHelper::getActionErrorContext($context, $this));
            throw new LightSamlContextException($context, $message);
        }

        if (
            $this->allowedFormat
            && $message->getIssuer()->getValue()
            && $message->getIssuer()->getFormat()
            && $message->getIssuer()->getFormat() != $this->allowedFormat
        ) {
            $message = sprintf(
                "Response Issuer Format if set must have value '%s' but it was '%s'",
                $this->allowedFormat,
                $message->getIssuer()->getFormat()
            );
            $this->logger->emergency($message, LogHelper::getActionErrorContext($context, $this));
            throw new LightSamlContextException($context, $message);
        }

        try {
            $this->nameIdValidator->validateNameId($message->getIssuer());
        } catch (LightSamlValidationException $ex) {
            throw new LightSamlContextException($context, $ex->getMessage(), 0, $ex);
        }
    }
}
