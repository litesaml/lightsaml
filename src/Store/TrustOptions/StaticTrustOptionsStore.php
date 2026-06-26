<?php

namespace LightSaml\Store\TrustOptions;

use LightSaml\Meta\TrustOptions\TrustOptions;

class StaticTrustOptionsStore implements TrustOptionsStoreInterface
{
    /** @var TrustOptions[] */
    protected $options = [];

    public function add(string $entityId, TrustOptions $options): static
    {
        $this->options[$entityId] = $options;

        return $this;
    }

    
    public function get(string $entityId): ?\LightSaml\Meta\TrustOptions\TrustOptions
    {
        return $this->options[$entityId] ?? null;
    }

    public function has(string $entityId): bool
    {
        return isset($this->options[$entityId]);
    }
}
