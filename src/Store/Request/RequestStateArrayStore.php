<?php

namespace LightSaml\Store\Request;

class RequestStateArrayStore extends AbstractRequestStateArrayStore
{
    /** @var array<string, \LightSaml\State\Request\RequestState> */
    private array $arrayStore = [];

    /** @return array<string, \LightSaml\State\Request\RequestState> */
    protected function getArray(): array
    {
        return $this->arrayStore;
    }

    /** @param array<string, \LightSaml\State\Request\RequestState> $arr */
    protected function setArray(array $arr): void
    {
        $this->arrayStore = $arr;
    }
}
