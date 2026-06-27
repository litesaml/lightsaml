<?php

namespace LightSaml\Store\Request;

use LightSaml\State\Request\RequestState;

abstract class AbstractRequestStateArrayStore implements RequestStateStoreInterface
{
    public function set(RequestState $state): void
    {
        $arr = $this->getArray();
        $arr[$state->getId()] = $state;
        $this->setArray($arr);
    }

    public function get(?string $id): ?RequestState
    {
        $result = null;
        $arr = $this->getArray();
        if (isset($arr[$id])) {
            $result = $arr[$id];
        }
        if ($result instanceof RequestState) {
            return $result;
        }

        return null;
    }

    public function remove(string $id): bool
    {
        $arr = $this->getArray();
        $result = isset($arr[$id]);
        unset($arr[$id]);
        $this->setArray($arr);

        return $result;
    }

    public function clear(): void
    {
        $this->setArray([]);
    }

    /** @return array<string, RequestState> */
    abstract protected function getArray(): array;

    /** @param array<string, RequestState> $arr */
    abstract protected function setArray(array $arr): void;
}
