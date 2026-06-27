<?php

namespace LightSaml\Model\XmlDSig;

use LightSaml\Model\AbstractSamlModel;

abstract class Signature extends AbstractSamlModel
{
    protected function getIDName(): string
    {
        return 'ID';
    }
}
