<?php

namespace LightSaml\Action\Assertion\Inbound;

use LightSaml\Action\Assertion\AbstractAssertionAction;
use LightSaml\Context\Profile\AssertionContext;
use LightSaml\Context\Profile\Helper\LogHelper;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Credential\CredentialInterface;
use LightSaml\Credential\Criteria\MetadataCriteria;
use LightSaml\Error\LightSamlContextException;
use LightSaml\Error\LightSamlModelException;
use LightSaml\Model\XmlDSig\AbstractSignatureReader;
use LightSaml\Model\XmlDSig\Signature;
use LightSaml\Validator\Model\Signature\SignatureValidatorInterface;
use Psr\Log\LoggerInterface;

class AssertionSignatureValidatorAction extends AbstractAssertionAction
{
    /**
     * @param bool $requireSignature
     */
    public function __construct(LoggerInterface $logger, protected SignatureValidatorInterface $signatureValidator, protected $requireSignature = true)
    {
        parent::__construct($logger);
    }

    protected function doExecute(AssertionContext $context): void
    {
        $signature = $context->getAssertion()->getSignature();
        if (!$signature instanceof Signature) {
            if ($this->requireSignature) {
                $message = 'Assertions must be signed';
                $this->logger->critical($message, LogHelper::getActionErrorContext($context, $this));
                throw new LightSamlContextException($context, $message);
            } else {
                $this->logger->debug('Assertion is not signed', LogHelper::getActionContext($context, $this));

                return;
            }
        }

        if ($signature instanceof AbstractSignatureReader) {
            $metadataType = ProfileContext::ROLE_IDP === $context->getProfileContext()->getOwnRole() ? MetadataCriteria::TYPE_SP : MetadataCriteria::TYPE_IDP;
            $credential = $this->signatureValidator->validate($signature, $context->getAssertion()->getIssuer()->getValue(), $metadataType);
            if ($credential instanceof CredentialInterface) {
                $keyNames = $credential->getKeyNames();
                $this->logger->debug(
                    sprintf('Assertion signature validated with key "%s"', implode(', ', $keyNames)),
                    LogHelper::getActionContext($context, $this, [
                        'credential' => $credential,
                    ])
                );
            } else {
                $this->logger->warning(
                    'Assertion signature verification was not performed',
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
