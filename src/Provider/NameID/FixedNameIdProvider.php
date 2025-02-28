<?php

namespace LightSaml\Provider\NameID;

use LightSaml\Context\Profile\AbstractProfileContext;
use LightSaml\Model\Assertion\NameID;

class FixedNameIdProvider implements NameIdProviderInterface
{
    public function __construct(protected ?NameID $nameId = null)
    {
    }

    /**
     * @return FixedNameIdProvider
     */
    public function setNameId(?NameID $nameId = null)
    {
        $this->nameId = $nameId;

        return $this;
    }

    /**
     * @return NameID|null
     */
    public function getNameID(AbstractProfileContext $context)
    {
        return $this->nameId;
    }
}
