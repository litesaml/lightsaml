<?php

namespace LightSaml\Build\Container;

use LightSaml\Provider\Attribute\AttributeValueProviderInterface;
use LightSaml\Provider\NameID\NameIdProviderInterface;
use LightSaml\Provider\Session\SessionInfoProviderInterface;

interface ProviderContainerInterface
{
    public function getAttributeValueProvider(): AttributeValueProviderInterface;

    public function getSessionInfoProvider(): SessionInfoProviderInterface;

    public function getNameIdProvider(): NameIdProviderInterface;
}
