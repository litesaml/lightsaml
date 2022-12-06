<?php

namespace LightSaml\Store\Request;

class RequestStateArrayStore extends AbstractRequestStateArrayStore
{
    private $arrayStore = [];

    /**
     * @return array
     */
    protected function getArray()
    {
        return $this->arrayStore;
    }

    protected function setArray(array $arr)
    {
        $this->arrayStore = $arr;
    }
}
