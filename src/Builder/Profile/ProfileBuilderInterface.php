<?php

namespace LightSaml\Builder\Profile;

use LightSaml\Action\CompositeAction;
use LightSaml\Context\Profile\ProfileContext;

interface ProfileBuilderInterface
{
    public function buildAction(): CompositeAction;

    public function buildContext(): ProfileContext;
}
