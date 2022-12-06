<?php

namespace LightSaml\Action;

interface ActionWrapperInterface
{
    /**
     * @return ActionInterface
     */
    public function wrap(ActionInterface $action);
}
