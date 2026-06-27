<?php

namespace LightSaml\Validator\Model\Assertion;

use LightSaml\Error\LightSamlValidationException;
use LightSaml\Model\Assertion\Assertion;

interface AssertionValidatorInterface
{
    /**
     * @throws LightSamlValidationException
     */
    public function validateAssertion(Assertion $assertion): void;
}
