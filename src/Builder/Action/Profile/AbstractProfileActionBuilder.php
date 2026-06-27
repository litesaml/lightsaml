<?php

namespace LightSaml\Builder\Action\Profile;

use LightSaml\Action\CompositeAction;
use LightSaml\Build\Container\BuildContainerInterface;
use LightSaml\Builder\Action\CompositeActionBuilder;
use LightSaml\Error\LightSamlBuildException;

abstract class AbstractProfileActionBuilder extends CompositeActionBuilder
{
    private bool $initialized = false;

    public function __construct(protected BuildContainerInterface $buildContainer)
    {
    }

    public function init(): void
    {
        if ($this->initialized) {
            throw new LightSamlBuildException('Already initialized');
        }

        $this->doInitialize();

        $this->initialized = true;
    }

    abstract protected function doInitialize(): void;

    public function build(): CompositeAction
    {
        if (false === $this->initialized) {
            $this->init();
        }

        return parent::build();
    }
}
