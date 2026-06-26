<?php

namespace LightSaml\Build\Container;

use LightSaml\Provider\Attribute\AttributeValueProviderInterface;
use LightSaml\Provider\NameID\NameIdProviderInterface;
use LightSaml\Provider\Session\SessionInfoProviderInterface;

interface ProviderContainerInterface
{
    public function getAttributeValueProvider(): \LightSaml\Provider\Attribute\AttributeValueProviderInterface;

    public function getSessionInfoProvider(): \LightSaml\Provider\Session\SessionInfoProviderInterface;

    public function getNameIdProvider(): \LightSaml\Provider\NameID\NameIdProviderInterface;
}
