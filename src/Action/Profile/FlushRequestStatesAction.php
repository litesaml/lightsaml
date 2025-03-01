<?php

namespace LightSaml\Action\Profile;

use LightSaml\Context\ContextInterface;
use LightSaml\Context\Profile\AssertionContext;
use LightSaml\Context\Profile\Helper\LogHelper;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Context\Profile\ProfileContexts;
use LightSaml\Context\Profile\RequestStateContext;
use LightSaml\Store\Request\RequestStateStoreInterface;
use Psr\Log\LoggerInterface;

class FlushRequestStatesAction extends AbstractProfileAction
{
    public function __construct(LoggerInterface $logger, protected RequestStateStoreInterface $requestStore)
    {
        parent::__construct($logger);
    }

    /**
     * @return void
     */
    protected function doExecute(ProfileContext $context)
    {
        $this->flush($context->getInboundContext()->getSubContext(ProfileContexts::REQUEST_STATE, null));
        foreach ($context as $child) {
            if ($child instanceof AssertionContext) {
                $this->flush($child->getSubContext(ProfileContexts::REQUEST_STATE, null));
            }
        }
    }

    /**
     * @param ContextInterface|null $requestStateContext
     */
    protected function flush($requestStateContext = null)
    {
        if (
            $requestStateContext instanceof RequestStateContext
            && $requestStateContext->getRequestState()
            && $requestStateContext->getRequestState()->getId()
        ) {
            $existed = $this->requestStore->remove($requestStateContext->getRequestState()->getId());

            if ($existed) {
                $this->logger->debug(
                    sprintf('Removed request state "%s"', $requestStateContext->getRequestState()->getId()),
                    LogHelper::getActionContext($requestStateContext, $this)
                );
            } else {
                $this->logger->warning(
                    sprintf('Request state "%s" does not exist', $requestStateContext->getRequestState()->getId()),
                    LogHelper::getActionContext($requestStateContext, $this)
                );
            }
        }
    }
}
