<?php

namespace LightSaml\Build\Container;

use LightSaml\Store\EntityDescriptor\EntityDescriptorStoreInterface;
use LightSaml\Store\TrustOptions\TrustOptionsStoreInterface;

interface PartyContainerInterface
{
    public function getIdpEntityDescriptorStore(): EntityDescriptorStoreInterface;

    public function getSpEntityDescriptorStore(): EntityDescriptorStoreInterface;

    public function getTrustOptionsStore(): TrustOptionsStoreInterface;
}
