<?php

namespace LightSaml\Builder\Profile;

use LightSaml\Action\CompositeAction;
use LightSaml\Context\Profile\ProfileContext;

interface ProfileBuilderInterface
{
    public function buildAction(): \LightSaml\Action\CompositeAction;

    public function buildContext(): \LightSaml\Context\Profile\ProfileContext;
}
