<?php

namespace LightSaml\Builder\Profile;

use LightSaml\Build\Container\BuildContainerInterface;
use LightSaml\Builder\Context\ProfileContextBuilder;

abstract class AbstractProfileBuilder implements ProfileBuilderInterface
{
    public function __construct(protected \LightSaml\Build\Container\BuildContainerInterface $container)
    {
    }

    /**
     * @return \LightSaml\Action\CompositeAction
     */
    public function buildAction()
    {
        return $this->getActionBuilder()->build();
    }

    /**
     * @return \LightSaml\Context\Profile\ProfileContext
     */
    public function buildContext()
    {
        $builder = new ProfileContextBuilder();
        $builder
            ->setProfileId($this->getProfileId())
            ->setRequest($this->container->getSystemContainer()->getRequest())
            ->setProfileRole($this->getProfileRole())
            ->setOwnEntityDescriptorProvider($this->container->getOwnContainer()->getOwnEntityDescriptorProvider())
        ;

        return $builder->build();
    }

    /**
     * @return string
     */
    abstract protected function getProfileId();

    /**
     * @return string
     */
    abstract protected function getProfileRole();

    /**
     * @return \LightSaml\Builder\Action\ActionBuilderInterface
     */
    abstract protected function getActionBuilder();
}
