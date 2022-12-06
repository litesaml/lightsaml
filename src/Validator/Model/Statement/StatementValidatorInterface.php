<?php

namespace LightSaml\Validator\Model\Statement;

use LightSaml\Model\Assertion\AbstractStatement;

interface StatementValidatorInterface
{
    /**
     * @return void
     */
    public function validateStatement(AbstractStatement $statement);
}
