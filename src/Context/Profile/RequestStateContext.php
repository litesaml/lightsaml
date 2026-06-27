<?php

namespace LightSaml\Context\Profile;

use LightSaml\State\Request\RequestState;

class RequestStateContext extends AbstractProfileContext
{
    protected RequestState $requestState;

    public function getRequestState(): RequestState
    {
        return $this->requestState;
    }

    public function setRequestState(RequestState $requestState): static
    {
        $this->requestState = $requestState;

        return $this;
    }
}
