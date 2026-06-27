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

    public function buildAction(): CompositeAction
    {
        return $this->getActionBuilder()->build();
    }

    public function buildContext(): ProfileContext
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

    abstract protected function getProfileId(): string;

    abstract protected function getProfileRole(): string;

    abstract protected function getActionBuilder(): ActionBuilderInterface;
}
