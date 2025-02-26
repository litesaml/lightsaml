<?php

namespace LightSaml\Validator\Model\Assertion;

use LightSaml\Error\LightSamlValidationException;
use LightSaml\Model\Assertion\Assertion;

interface AssertionTimeValidatorInterface
{
    /**
     * @param int $now
     * @param int $allowedSecondsSkew
     *
     * @throws LightSamlValidationException
     *
     * @return void
     */
    public function validateTimeRestrictions(Assertion $assertion, $now, $allowedSecondsSkew);
}
