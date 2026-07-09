<?php

namespace LightSaml\Action\Profile\Inbound\Response;

use LightSaml\Action\Profile\AbstractProfileAction;
use LightSaml\Context\Profile\Helper\LogHelper;
use LightSaml\Context\Profile\Helper\MessageContextHelper;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Error\LightSamlContextException;

/**
 * Rejects an inbound Response that contains more than one assertion sharing the
 * same ID. An assertion ID is an xsd:ID and MUST be unique within a document, so
 * a Response that reuses one is malformed. This is a stateless, per-document
 * check.
 */
class UniqueAssertionIdValidatorAction extends AbstractProfileAction
{
    protected function doExecute(ProfileContext $context): void
    {
        $response = MessageContextHelper::asResponse($context->getInboundContext());

        $seen = [];
        foreach ($response->getAllAssertions() as $assertion) {
            $id = $assertion->getId();
            if ($id === null || $id === '') {
                continue;
            }
            if (isset($seen[$id])) {
                $message = sprintf('Response contains more than one assertion with ID "%s"', $id);
                $this->logger->error($message, LogHelper::getActionErrorContext($context, $this));

                throw new LightSamlContextException($context, $message);
            }
            $seen[$id] = true;
        }
    }
}
