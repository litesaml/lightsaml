<?php

namespace LightSaml\Validator\Model\Assertion;

use LightSaml\Error\LightSamlValidationException;
use LightSaml\Model\Assertion\Assertion;

interface AssertionTimeValidatorInterface
{
    /**
     * @throws LightSamlValidationException
     *
     */
    public function validateTimeRestrictions(Assertion $assertion, int $now, int $allowedSecondsSkew): void;
}
