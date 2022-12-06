<?php

namespace LightSaml\Validator\Model\NameId;

use LightSaml\Model\Assertion\AbstractNameID;

interface NameIdValidatorInterface
{
    /**
     * @return void
     */
    public function validateNameId(AbstractNameID $nameId);
}
