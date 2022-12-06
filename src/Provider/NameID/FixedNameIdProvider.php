<?php

namespace LightSaml\Provider\NameID;

use LightSaml\Context\Profile\AbstractProfileContext;
use LightSaml\Model\Assertion\NameID;

class FixedNameIdProvider implements NameIdProviderInterface
{
    /** @var NameID|null */
    protected $nameId;

    public function __construct(NameID $nameId = null)
    {
        $this->nameId = $nameId;
    }

    /**
     * @return FixedNameIdProvider
     */
    public function setNameId(NameID $nameId = null)
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
