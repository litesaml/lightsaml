<?php

namespace LightSaml\Builder\Profile;

use LightSaml\Action\CompositeAction;
use LightSaml\Context\Profile\ProfileContext;

interface ProfileBuilderInterface
{
    /**
     * @return CompositeAction
     */
    public function buildAction();

    /**
     * @return ProfileContext
     */
    public function buildContext();
}
