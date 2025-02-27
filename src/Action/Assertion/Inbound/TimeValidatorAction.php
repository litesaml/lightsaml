<?php

namespace LightSaml\Action\Assertion\Inbound;

use LightSaml\Action\Assertion\AbstractAssertionAction;
use LightSaml\Context\Profile\AssertionContext;
use LightSaml\Provider\TimeProvider\TimeProviderInterface;
use LightSaml\Validator\Model\Assertion\AssertionTimeValidatorInterface;
use Psr\Log\LoggerInterface;

class TimeValidatorAction extends AbstractAssertionAction
{
    /**
     * @param int $allowedSecondsSkew
     */
    public function __construct(
        LoggerInterface $logger,
        protected AssertionTimeValidatorInterface $assertionTimeValidator,
        protected TimeProviderInterface $timeProvider,
        protected $allowedSecondsSkew = 120
    ) {
        parent::__construct($logger);
    }

    /**
     * @return void
     */
    protected function doExecute(AssertionContext $context)
    {
        $this->assertionTimeValidator->validateTimeRestrictions(
            $context->getAssertion(),
            $this->timeProvider->getTimestamp(),
            $this->allowedSecondsSkew
        );
    }
}
