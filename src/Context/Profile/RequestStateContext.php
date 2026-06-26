<?php

namespace LightSaml\Context\Profile;

use LightSaml\State\Request\RequestState;

class RequestStateContext extends AbstractProfileContext
{
    /** @var RequestState */
    protected $requestState;

    public function getRequestState(): \LightSaml\State\Request\RequestState
    {
        return $this->requestState;
    }

    public function setRequestState(\LightSaml\State\Request\RequestState $requestState): static
    {
        $this->requestState = $requestState;

        return $this;
    }
}
