<?php

namespace LightSaml\Store\TrustOptions;

use LightSaml\Meta\TrustOptions\TrustOptions;

class StaticTrustOptionsStore implements TrustOptionsStoreInterface
{
    /** @var TrustOptions[] */
    protected $options = [];

    /**
     * @param string $entityId
     *
     * @return StaticTrustOptionsStore
     */
    public function add($entityId, TrustOptions $options)
    {
        $this->options[$entityId] = $options;

        return $this;
    }

    /**
     * @param string $entityId
     *
     * @return TrustOptions|null
     */
    public function get($entityId)
    {
        return isset($this->options[$entityId]) ? $this->options[$entityId] : null;
    }

    /**
     * @param string $entityId
     *
     * @return bool
     */
    public function has($entityId)
    {
        return isset($this->options[$entityId]);
    }
}
