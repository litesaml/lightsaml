<?php

namespace LightSaml\Store\Sso;

use LightSaml\State\Sso\SsoState;

interface SsoStateStoreInterface
{
    public function get(): SsoState;

    public function set(SsoState $ssoState): void;
}
