<?php

namespace LightSaml\Provider\NameID;

use LightSaml\Context\Profile\AbstractProfileContext;
use LightSaml\Model\Assertion\NameID;

class FixedNameIdProvider implements NameIdProviderInterface
{
    public function __construct(protected ?NameID $nameId = null)
    {
    }

    public function setNameId(?NameID $nameId = null): static
    {
        $this->nameId = $nameId;

        return $this;
    }

    public function getNameID(AbstractProfileContext $context): ?\LightSaml\Model\Assertion\NameID
    {
        return $this->nameId;
    }
}
