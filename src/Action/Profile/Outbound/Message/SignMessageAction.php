<?php

namespace LightSaml\Action\Profile\Outbound\Message;

use LightSaml\Action\Profile\AbstractProfileAction;
use LightSaml\Context\Profile\Helper\LogHelper;
use LightSaml\Context\Profile\Helper\MessageContextHelper;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Model\Protocol\AuthnRequest;
use LightSaml\Model\Protocol\LogoutRequest;
use LightSaml\Model\Protocol\Response;
use LightSaml\Resolver\Signature\SignatureResolverInterface;
use LogicException;
use Psr\Log\LoggerInterface;

/**
 * Signs the outbound message, according to TrustOptions settings.
 */
class SignMessageAction extends AbstractProfileAction
{
    public function __construct(LoggerInterface $logger, protected SignatureResolverInterface $signatureResolver)
    {
        parent::__construct($logger);
    }

    protected function doExecute(ProfileContext $context)
    {
        $shouldSign = $this->shouldSignMessage($context);
        if ($shouldSign) {
            $signature = $this->signatureResolver->getSignature($context);
            if ($signature) {
                MessageContextHelper::asSamlMessage($context->getOutboundContext())
                    ->setSignature($signature)
                ;

                $this->logger->debug(
                    sprintf('Message signed with fingerprint "%s"', $signature->getCertificate()->getFingerprint()),
                    LogHelper::getActionContext($context, $this, [
                        'certificate' => $signature->getCertificate()->getInfo(),
                    ])
                );
            } else {
                $this->logger->critical(
                    'No signature resolved, although signing enabled',
                    LogHelper::getActionErrorContext($context, $this, [])
                );
            }
        } else {
            $this->logger->debug('Signing disabled', LogHelper::getActionContext($context, $this));
        }
    }

    /**
     * @return bool
     */
    private function shouldSignMessage(ProfileContext $context)
    {
        $message = $context->getOutboundMessage();

        if ($message instanceof LogoutRequest) {
            return true;
        }

        $trustOptions = $context->getTrustOptions();

        if ($message instanceof AuthnRequest) {
            return $trustOptions->getSignAuthnRequest();
        } elseif ($message instanceof Response) {
            return $trustOptions->getSignResponse();
        }

        throw new LogicException(sprintf('Unexpected message type "%s"', $message::class));
    }
}
