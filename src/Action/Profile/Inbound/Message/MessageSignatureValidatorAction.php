<?php

namespace LightSaml\Action\Profile\Inbound\Message;

use LightSaml\Action\Profile\AbstractProfileAction;
use LightSaml\Context\Profile\Helper\LogHelper;
use LightSaml\Context\Profile\Helper\MessageContextHelper;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Credential\Criteria\MetadataCriteria;
use LightSaml\Error\LightSamlModelException;
use LightSaml\Model\XmlDSig\AbstractSignatureReader;
use LightSaml\Validator\Model\Signature\SignatureValidatorInterface;
use Psr\Log\LoggerInterface;

/**
 * Validates the signature, if any, of the inbound message.
 */
class MessageSignatureValidatorAction extends AbstractProfileAction
{
    public function __construct(LoggerInterface $logger, protected SignatureValidatorInterface $signatureValidator)
    {
        parent::__construct($logger);
    }

    /**
     * @return void
     */
    protected function doExecute(ProfileContext $context)
    {
        $message = MessageContextHelper::asSamlMessage($context->getInboundContext());

        $signature = $message->getSignature();
        if (null === $signature) {
            $this->logger->debug('Message is not signed', LogHelper::getActionContext($context, $this));

            return;
        }

        if ($signature instanceof AbstractSignatureReader) {
            $metadataType = ProfileContext::ROLE_IDP === $context->getOwnRole() ? MetadataCriteria::TYPE_SP : MetadataCriteria::TYPE_IDP;
            $credential = $this->signatureValidator->validate($signature, $message->getIssuer()->getValue(), $metadataType);
            if ($credential) {
                $keyNames = $credential->getKeyNames();
                $this->logger->debug(
                    sprintf('Message signature validated with key "%s"', implode(', ', $keyNames)),
                    LogHelper::getActionContext($context, $this, [
                        'credential' => $credential,
                    ])
                );
            } else {
                $this->logger->warning(
                    'Signature verification was not performed',
                    LogHelper::getActionContext($context, $this)
                );
            }
        } else {
            $message = 'Expected AbstractSignatureReader';
            $this->logger->critical($message, LogHelper::getActionErrorContext($context, $this));
            throw new LightSamlModelException($message);
        }
    }
}
