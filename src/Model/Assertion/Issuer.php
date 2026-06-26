<?php

namespace LightSaml\Model\Assertion;

class Issuer extends AbstractNameID
{
    protected function getElementName(): string
    {
        return 'Issuer';
    }
}
