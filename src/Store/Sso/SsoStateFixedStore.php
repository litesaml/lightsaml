<?php

namespace LightSaml\Store\Sso;

use LightSaml\State\Sso\SsoState;

class SsoStateFixedStore implements SsoStateStoreInterface
{
    /** @var SsoState */
    protected $ssoState;

    public function get(): \LightSaml\State\Sso\SsoState
    {
        if (null == $this->ssoState) {
            $this->ssoState = new SsoState();
        }

        return $this->ssoState;
    }

    public function set(SsoState $ssoState): void
    {
        $this->ssoState = $ssoState;
    }
}
