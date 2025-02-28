<?php

namespace LightSaml\Builder\Profile;

use LightSaml\Action\CompositeAction;
use LightSaml\Build\Container\BuildContainerInterface;
use LightSaml\Builder\Action\ActionBuilderInterface;
use LightSaml\Builder\Context\ProfileContextBuilder;
use LightSaml\Context\Profile\ProfileContext;

abstract class AbstractProfileBuilder implements ProfileBuilderInterface
{
    public function __construct(protected BuildContainerInterface $container)
    {
    }

    /**
     * @return CompositeAction
     */
    public function buildAction()
    {
        return $this->getActionBuilder()->build();
    }

    /**
     * @return ProfileContext
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
     * @return ActionBuilderInterface
     */
    abstract protected function getActionBuilder();
}
