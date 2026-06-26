<?php

namespace LightSaml\Action;

interface CompositeActionInterface extends ActionInterface
{
    public function add(ActionInterface $action): \LightSaml\Action\CompositeActionInterface;

    
    public function map(callable $callable): void;
}
