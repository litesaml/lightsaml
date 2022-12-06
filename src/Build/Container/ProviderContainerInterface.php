<?php

namespace LightSaml\Build\Container;

use LightSaml\Provider\Attribute\AttributeValueProviderInterface;
use LightSaml\Provider\NameID\NameIdProviderInterface;
use LightSaml\Provider\Session\SessionInfoProviderInterface;

interface ProviderContainerInterface
{
    /**
     * @return AttributeValueProviderInterface
     */
    public function getAttributeValueProvider();

    /**
     * @return SessionInfoProviderInterface
     */
    public function getSessionInfoProvider();

    /**
     * @return NameIdProviderInterface
     */
    public function getNameIdProvider();
}
