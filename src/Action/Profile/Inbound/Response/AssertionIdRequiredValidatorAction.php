<?php

namespace LightSaml\Action\Profile\Inbound\Response;

use LightSaml\Action\Profile\AbstractProfileAction;
use LightSaml\Context\Profile\Helper\LogHelper;
use LightSaml\Context\Profile\Helper\MessageContextHelper;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Error\LightSamlContextException;

/**
 * Rejects an inbound Response containing an assertion with a missing or empty
 * ID. ID is a required xsd:ID attribute on saml:Assertion per the SAML schema.
 */
class AssertionIdRequiredValidatorAction extends AbstractProfileAction
{
    protected function doExecute(ProfileContext $context): void
    {
        $response = MessageContextHelper::asResponse($context->getInboundContext());

        foreach ($response->getAllAssertions() as $assertion) {
            $id = $assertion->getId();
            if ($id === null || $id === '') {
                $message = 'Response contains an assertion with a missing or empty ID';
                $this->logger->error($message, LogHelper::getActionErrorContext($context, $this));

                throw new LightSamlContextException($context, $message);
            }
        }
    }
}
