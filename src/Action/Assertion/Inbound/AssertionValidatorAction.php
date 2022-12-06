<?php

namespace LightSaml\Action\Assertion\Inbound;

use LightSaml\Action\Assertion\AbstractAssertionAction;
use LightSaml\Context\Profile\AssertionContext;
use LightSaml\Validator\Model\Assertion\AssertionValidatorInterface;
use Psr\Log\LoggerInterface;

class AssertionValidatorAction extends AbstractAssertionAction
{
    /** @var AssertionValidatorInterface */
    protected $assertionValidator;

    public function __construct(LoggerInterface $logger, AssertionValidatorInterface $assertionValidator)
    {
        parent::__construct($logger);

        $this->assertionValidator = $assertionValidator;
    }

    protected function doExecute(AssertionContext $context)
    {
        $this->assertionValidator->validateAssertion($context->getAssertion());
    }
}
