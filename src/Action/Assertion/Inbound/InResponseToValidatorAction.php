<?php

namespace LightSaml\Action\Assertion\Inbound;

use LightSaml\Action\Assertion\AbstractAssertionAction;
use LightSaml\Context\Profile\AssertionContext;
use LightSaml\Context\Profile\Helper\LogHelper;
use LightSaml\Context\Profile\ProfileContexts;
use LightSaml\Context\Profile\RequestStateContext;
use LightSaml\Error\LightSamlContextException;
use LightSaml\State\Request\RequestState;
use LightSaml\Store\Request\RequestStateStoreInterface;
use Psr\Log\LoggerInterface;

class InResponseToValidatorAction extends AbstractAssertionAction
{
    public function __construct(LoggerInterface $logger, protected RequestStateStoreInterface $requestStore)
    {
        parent::__construct($logger);
    }

    protected function doExecute(AssertionContext $context)
    {
        if (null === $context->getAssertion()->getSubject()) {
            return;
        }

        foreach ($context->getAssertion()->getSubject()->getAllSubjectConfirmations() as $subjectConfirmation) {
            if (
                $subjectConfirmation->getSubjectConfirmationData()
                && $subjectConfirmation->getSubjectConfirmationData()->getInResponseTo()
            ) {
                $requestState = $this->validateInResponseTo(
                    $subjectConfirmation->getSubjectConfirmationData()->getInResponseTo(),
                    $context
                );

                /** @var RequestStateContext $requestStateContext */
                $requestStateContext = $context->getSubContext(ProfileContexts::REQUEST_STATE, RequestStateContext::class);
                $requestStateContext->setRequestState($requestState);
            }
        }
    }

    /**
     * @param string $inResponseTo
     *
     * @return RequestState
     */
    protected function validateInResponseTo($inResponseTo, AssertionContext $context)
    {
        $requestState = $this->requestStore->get($inResponseTo);
        if (null == $requestState) {
            $message = sprintf("Unknown InResponseTo '%s'", $inResponseTo);
            $this->logger->emergency($message, LogHelper::getActionErrorContext($context, $this));
            throw new LightSamlContextException($context, $message);
        }

        return $requestState;
    }
}
