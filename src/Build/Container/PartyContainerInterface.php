<?php

namespace LightSaml\Build\Container;

use LightSaml\Store\EntityDescriptor\EntityDescriptorStoreInterface;
use LightSaml\Store\TrustOptions\TrustOptionsStoreInterface;

interface PartyContainerInterface
{
    public function getIdpEntityDescriptorStore(): \LightSaml\Store\EntityDescriptor\EntityDescriptorStoreInterface;

    public function getSpEntityDescriptorStore(): \LightSaml\Store\EntityDescriptor\EntityDescriptorStoreInterface;

    public function getTrustOptionsStore(): \LightSaml\Store\TrustOptions\TrustOptionsStoreInterface;
}
