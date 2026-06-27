<?php

namespace LightSaml\Store\Request;

class RequestStateArrayStore extends AbstractRequestStateArrayStore
{
    private array $arrayStore = [];

    protected function getArray(): array
    {
        return $this->arrayStore;
    }

    protected function setArray(array $arr)
    {
        $this->arrayStore = $arr;
    }
}
