<?php

namespace LightSaml\Store\TrustOptions;

use LightSaml\Meta\TrustOptions\TrustOptions;

class FixedTrustOptionsStore implements TrustOptionsStoreInterface
{
    /**
     */
    public function __construct(protected ?TrustOptions $option = null)
    {
    }

    /**
     * @return FixedTrustOptionsStore
     */
    public function setTrustOptions(?TrustOptions $trustOptions = null)
    {
        $this->option = $trustOptions;

        return $this;
    }

    /**
     * @param string $entityId
     *
     * @return TrustOptions|null
     */
    public function get($entityId)
    {
        return $this->option;
    }

    /**
     * @param string $entityId
     *
     * @return bool
     */
    public function has($entityId)
    {
        return true;
    }
}
