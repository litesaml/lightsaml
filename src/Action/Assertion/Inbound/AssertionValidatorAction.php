<?php

namespace LightSaml\Action\Assertion\Inbound;

use LightSaml\Action\Assertion\AbstractAssertionAction;
use LightSaml\Context\Profile\AssertionContext;
use LightSaml\Validator\Model\Assertion\AssertionValidatorInterface;
use Psr\Log\LoggerInterface;

class AssertionValidatorAction extends AbstractAssertionAction
{
    public function __construct(LoggerInterface $logger, protected AssertionValidatorInterface $assertionValidator)
    {
        parent::__construct($logger);
    }

    protected function doExecute(AssertionContext $context)
    {
        $this->assertionValidator->validateAssertion($context->getAssertion());
    }
}
