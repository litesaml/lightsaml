<?php

namespace LightSaml\Store\Sso;

use LightSaml\State\Sso\SsoState;

interface SsoStateStoreInterface
{
    public function get(): \LightSaml\State\Sso\SsoState;

    public function set(SsoState $ssoState): void;
}
