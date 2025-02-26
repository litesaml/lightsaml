<?php

namespace LightSaml\Action\Profile\Inbound\Message;

use LightSaml\Action\Profile\AbstractProfileAction;
use LightSaml\Context\Profile\Helper\LogHelper;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Error\LightSamlContextException;
use LightSaml\Meta\TrustOptions\TrustOptions;
use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\Store\EntityDescriptor\EntityDescriptorStoreInterface;
use LightSaml\Store\TrustOptions\TrustOptionsStoreInterface;
use Psr\Log\LoggerInterface;

/**
 * Looks up inbound message Issuer in entity descriptor providers and sets it to the party context.
 */
class ResolvePartyEntityIdAction extends AbstractProfileAction
{
    public function __construct(
        LoggerInterface $logger,
        private readonly EntityDescriptorStoreInterface $spEntityDescriptorProvider,
        private readonly EntityDescriptorStoreInterface $idpEntityDescriptorProvider,
        protected TrustOptionsStoreInterface $trustOptionsProvider
    ) {
        parent::__construct($logger);
    }

    protected function doExecute(ProfileContext $context)
    {
        $partyContext = $context->getPartyEntityContext();

        if ($partyContext->getEntityDescriptor() && $partyContext->getTrustOptions()) {
            $this->logger->debug(
                sprintf('Party EntityDescriptor and TrustOptions already set for "%s"', $partyContext->getEntityDescriptor()->getEntityID()),
                LogHelper::getActionContext($context, $this, [
                    'partyEntityId' => $partyContext->getEntityDescriptor()->getEntityID(),
                ])
            );

            return;
        }

        $entityId = $partyContext->getEntityDescriptor() ? $partyContext->getEntityDescriptor()->getEntityID() : null;
        $entityId = $entityId ?: $partyContext->getEntityId();
        if (null == $entityId) {
            $message = 'EntityID is not set in the party context';
            $this->logger->critical($message, LogHelper::getActionErrorContext($context, $this));
            throw new LightSamlContextException($context, $message);
        }

        if (null == $partyContext->getEntityDescriptor()) {
            $partyEntityDescriptor = $this->getPartyEntityDescriptor(
                $context,
                ProfileContext::ROLE_IDP === $context->getOwnRole()
                ? $this->spEntityDescriptorProvider
                : $this->idpEntityDescriptorProvider,
                $context->getPartyEntityContext()->getEntityId()
            );
            $partyContext->setEntityDescriptor($partyEntityDescriptor);
            $this->logger->debug(
                sprintf('Known issuer resolved: "%s"', $partyEntityDescriptor->getEntityID()),
                LogHelper::getActionContext($context, $this, [
                    'partyEntityId' => $partyEntityDescriptor->getEntityID(),
                ])
            );
        }

        if (null == $partyContext->getTrustOptions()) {
            $trustOptions = $this->trustOptionsProvider->get($partyContext->getEntityDescriptor()->getEntityID());
            if (null === $trustOptions) {
                $trustOptions = new TrustOptions();
            }
            $partyContext->setTrustOptions($trustOptions);
        }
    }

    /**
     * @param string $entityId
     *
     * @return EntityDescriptor
     */
    protected function getPartyEntityDescriptor(
        ProfileContext $context,
        EntityDescriptorStoreInterface $entityDescriptorProvider,
        $entityId
    ) {
        $partyEntityDescriptor = $entityDescriptorProvider->get($entityId);
        if (null === $partyEntityDescriptor) {
            $message = sprintf("Unknown issuer '%s'", $entityId);
            $this->logger->emergency($message, LogHelper::getActionErrorContext($context, $this));
            throw new LightSamlContextException($context, $message);
        }

        return $partyEntityDescriptor;
    }
}
