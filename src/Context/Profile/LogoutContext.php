<?php

namespace LightSaml\Context\Profile;

use LightSaml\State\Sso\SsoSessionState;

class LogoutContext extends AbstractProfileContext
{
    protected ?SsoSessionState $ssoSessionState = null;

    protected bool $allSsoSessionsTerminated = false;

    public function getSsoSessionState(): ?SsoSessionState
    {
        return $this->ssoSessionState;
    }

    public function setSsoSessionState(SsoSessionState $ssoSessionState): static
    {
        $this->ssoSessionState = $ssoSessionState;
        $this->allSsoSessionsTerminated = false;

        return $this;
    }

    public function areAllSsoSessionsTerminated(): bool
    {
        return $this->allSsoSessionsTerminated;
    }

    public function setAllSsoSessionsTerminated(bool $allSsoSessionsTerminated): static
    {
        $this->allSsoSessionsTerminated = $allSsoSessionsTerminated;

        return $this;
    }
}
