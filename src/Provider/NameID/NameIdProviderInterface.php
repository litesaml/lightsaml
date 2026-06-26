<?php

namespace LightSaml\Provider\NameID;

use LightSaml\Context\Profile\AbstractProfileContext;
use LightSaml\Model\Assertion\NameID;

interface NameIdProviderInterface
{
    public function getNameID(AbstractProfileContext $context): ?\LightSaml\Model\Assertion\NameID;
}
