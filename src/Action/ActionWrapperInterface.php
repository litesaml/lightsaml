<?php

namespace LightSaml\Action;

interface ActionWrapperInterface
{
    public function wrap(ActionInterface $action): ActionInterface;
}
