<?php

namespace LightSaml\Build\Container;

use LightSaml\Store\Id\IdStoreInterface;
use LightSaml\Store\Request\RequestStateStoreInterface;
use LightSaml\Store\Sso\SsoStateStoreInterface;

interface StoreContainerInterface
{
    public function getRequestStateStore(): RequestStateStoreInterface;

    public function getIdStateStore(): IdStoreInterface;

    public function getSsoStateStore(): SsoStateStoreInterface;
}
