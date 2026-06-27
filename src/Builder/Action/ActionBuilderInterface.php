<?php

namespace LightSaml\Builder\Action;

use LightSaml\Action\ActionInterface;

interface ActionBuilderInterface
{
    public function build(): ActionInterface;
}
