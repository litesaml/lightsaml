<?php

namespace LightSaml\Validator\Model\NameId;

use LightSaml\Model\Assertion\AbstractNameID;

interface NameIdValidatorInterface
{
    public function validateNameId(AbstractNameID $nameId): void;
}
