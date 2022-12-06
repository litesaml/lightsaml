<?php

namespace LightSaml\Validator\Model\Assertion;

use LightSaml\Model\Assertion\Assertion;

interface AssertionValidatorInterface
{
    /**
     * @throws \LightSaml\Error\LightSamlValidationException
     *
     * @return void
     */
    public function validateAssertion(Assertion $assertion);
}
