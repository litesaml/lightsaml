<?php

namespace LightSaml\Validator\Model\Statement;

use LightSaml\Model\Assertion\AbstractStatement;

interface StatementValidatorInterface
{
    public function validateStatement(AbstractStatement $statement): void;
}
