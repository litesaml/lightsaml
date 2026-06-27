<?php

namespace LightSaml\Model\Assertion;

class NameID extends AbstractNameID
{
    protected function getElementName(): string
    {
        return 'NameID';
    }
}
