<?php

namespace LightSaml\Build\Container;

interface BuildContainerInterface
{
    public function getSystemContainer(): SystemContainerInterface;

    public function getPartyContainer(): PartyContainerInterface;

    public function getStoreContainer(): StoreContainerInterface;

    public function getProviderContainer(): ProviderContainerInterface;

    public function getCredentialContainer(): CredentialContainerInterface;

    public function getServiceContainer(): ServiceContainerInterface;

    public function getOwnContainer(): OwnContainerInterface;
}
