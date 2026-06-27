<?php

namespace LightSaml\Builder\Action;

use LightSaml\Action\CompositeAction;

interface ActionBuilderInterface
{
    public function build(): CompositeAction;
}
