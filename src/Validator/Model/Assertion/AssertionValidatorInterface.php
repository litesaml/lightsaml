<?php

namespace LightSaml\Validator\Model\Assertion;

use LightSaml\Error\LightSamlValidationException;
use LightSaml\Model\Assertion\Assertion;

interface AssertionValidatorInterface
{
    /**
     * @throws LightSamlValidationException
     *
     * @return void
     */
    public function validateAssertion(Assertion $assertion);
}
