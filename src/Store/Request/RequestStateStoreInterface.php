<?php

namespace LightSaml\Store\Request;

use LightSaml\State\Request\RequestState;

interface RequestStateStoreInterface
{
    public function set(RequestState $state): void;

    public function get(?string $id): ?RequestState;

    public function remove(string $id): bool;

    public function clear(): void;
}
