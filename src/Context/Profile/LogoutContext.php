<?php

namespace LightSaml\Context\Profile;

use LightSaml\State\Sso\SsoSessionState;

class LogoutContext extends AbstractProfileContext
{
    /** @var SsoSessionState|null */
    protected $ssoSessionState;

    /** @var bool */
    protected $allSsoSessionsTerminated = false;

    /**
     * @return SsoSessionState|null
     */
    public function getSsoSessionState()
    {
        return $this->ssoSessionState;
    }

    /**
     * @return LogoutContext
     */
    public function setSsoSessionState(SsoSessionState $ssoSessionState)
    {
        $this->ssoSessionState = $ssoSessionState;
        $this->allSsoSessionsTerminated = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function areAllSsoSessionsTerminated()
    {
        return $this->allSsoSessionsTerminated;
    }

    /**
     * @param bool $allSsoSessionsTerminated
     *
     * @return LogoutContext
     */
    public function setAllSsoSessionsTerminated($allSsoSessionsTerminated)
    {
        $this->allSsoSessionsTerminated = (bool) $allSsoSessionsTerminated;

        return $this;
    }
}
