<?php

namespace LightSaml\Build\Container;

use LightSaml\Store\Id\IdStoreInterface;
use LightSaml\Store\Request\RequestStateStoreInterface;
use LightSaml\Store\Sso\SsoStateStoreInterface;

interface StoreContainerInterface
{
    public function getRequestStateStore(): \LightSaml\Store\Request\RequestStateStoreInterface;

    public function getIdStateStore(): \LightSaml\Store\Id\IdStoreInterface;

    public function getSsoStateStore(): \LightSaml\Store\Sso\SsoStateStoreInterface;
}
