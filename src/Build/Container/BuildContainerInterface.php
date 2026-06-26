<?php

namespace LightSaml\Build\Container;

interface BuildContainerInterface
{
    public function getSystemContainer(): \LightSaml\Build\Container\SystemContainerInterface;

    public function getPartyContainer(): \LightSaml\Build\Container\PartyContainerInterface;

    public function getStoreContainer(): \LightSaml\Build\Container\StoreContainerInterface;

    public function getProviderContainer(): \LightSaml\Build\Container\ProviderContainerInterface;

    public function getCredentialContainer(): \LightSaml\Build\Container\CredentialContainerInterface;

    public function getServiceContainer(): \LightSaml\Build\Container\ServiceContainerInterface;

    public function getOwnContainer(): \LightSaml\Build\Container\OwnContainerInterface;
}
