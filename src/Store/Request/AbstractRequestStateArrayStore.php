<?php

namespace LightSaml\Store\Request;

use LightSaml\State\Request\RequestState;

abstract class AbstractRequestStateArrayStore implements RequestStateStoreInterface
{
    /**
     * @return AbstractRequestStateArrayStore
     */
    public function set(RequestState $state)
    {
        $arr = $this->getArray();
        $arr[$state->getId()] = $state;
        $this->setArray($arr);

        return $this;
    }

    /**
     * @param string $id
     *
     * @return RequestState|null
     */
    public function get($id)
    {
        $result = null;
        $arr = $this->getArray();
        if (false == is_array($arr)) {
            $arr = [];
            $this->setArray($arr);
        }
        if (isset($arr[$id])) {
            $result = $arr[$id];
        }
        if ($result instanceof RequestState) {
            return $result;
        }

        return;
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function remove($id)
    {
        $arr = $this->getArray();
        $result = isset($arr[$id]);
        unset($arr[$id]);
        $this->setArray($arr);

        return $result;
    }

    /**
     * @return void
     */
    public function clear()
    {
        $this->setArray([]);
    }

    /**
     * @return array
     */
    abstract protected function getArray();

    abstract protected function setArray(array $arr);
}
