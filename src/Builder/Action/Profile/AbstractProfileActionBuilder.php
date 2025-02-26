<?php

namespace LightSaml\Builder\Action\Profile;

use LightSaml\Action\ActionInterface;
use LightSaml\Build\Container\BuildContainerInterface;
use LightSaml\Builder\Action\CompositeActionBuilder;
use LightSaml\Error\LightSamlBuildException;

abstract class AbstractProfileActionBuilder extends CompositeActionBuilder
{
    /** @var bool */
    private $initialized = false;

    public function __construct(protected BuildContainerInterface $buildContainer)
    {
    }

    /**
     * @return void
     */
    public function init()
    {
        if ($this->initialized) {
            throw new LightSamlBuildException('Already initialized');
        }

        $this->doInitialize();

        $this->initialized = true;
    }

    /**
     * @return void
     */
    abstract protected function doInitialize();

    /**
     * @return ActionInterface
     */
    public function build()
    {
        if (false === $this->initialized) {
            $this->init();
        }

        return parent::build();
    }
}
