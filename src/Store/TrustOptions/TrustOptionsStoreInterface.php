<?php

namespace LightSaml\Store\TrustOptions;

use LightSaml\Meta\TrustOptions\TrustOptions;

interface TrustOptionsStoreInterface
{
    
    public function get(string $entityId): ?\LightSaml\Meta\TrustOptions\TrustOptions;

    
    public function has(string $entityId): bool;
}
