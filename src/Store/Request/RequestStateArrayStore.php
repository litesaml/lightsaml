<?php

namespace LightSaml\Store\Request;

use LightSaml\State\Request\RequestState;

class RequestStateArrayStore extends AbstractRequestStateArrayStore
{
    /** @var array<string, RequestState> */
    private array $arrayStore = [];

    /** @return array<string, RequestState> */
    protected function getArray(): array
    {
        return $this->arrayStore;
    }

    /** @param array<string, RequestState> $arr */
    protected function setArray(array $arr): void
    {
        $this->arrayStore = $arr;
    }
}
