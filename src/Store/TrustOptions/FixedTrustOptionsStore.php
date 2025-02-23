<?php

namespace LightSaml\Store\TrustOptions;

use LightSaml\Meta\TrustOptions\TrustOptions;

class FixedTrustOptionsStore implements TrustOptionsStoreInterface
{
    /**
     * @param TrustOptions $option
     */
    public function __construct(protected ?\LightSaml\Meta\TrustOptions\TrustOptions $option = null)
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
