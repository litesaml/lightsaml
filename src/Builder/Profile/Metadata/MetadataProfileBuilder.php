<?php

namespace LightSaml\Builder\Profile\Metadata;

use LightSaml\Builder\Action\ActionBuilderInterface;
use LightSaml\Builder\Action\Profile\Metadata\MetadataActionBuilder;
use LightSaml\Builder\Profile\AbstractProfileBuilder;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Profile\Profiles;

class MetadataProfileBuilder extends AbstractProfileBuilder
{
    /**
     * @return string
     */
    protected function getProfileId()
    {
        return Profiles::METADATA;
    }

    /**
     * @return string
     */
    protected function getProfileRole()
    {
        return ProfileContext::ROLE_NONE;
    }

    /**
     * @return ActionBuilderInterface
     */
    protected function getActionBuilder()
    {
        return new MetadataActionBuilder($this->container);
    }
}
