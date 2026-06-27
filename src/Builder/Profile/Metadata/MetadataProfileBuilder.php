<?php

namespace LightSaml\Builder\Profile\Metadata;

use LightSaml\Builder\Action\ActionBuilderInterface;
use LightSaml\Builder\Action\Profile\Metadata\MetadataActionBuilder;
use LightSaml\Builder\Profile\AbstractProfileBuilder;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Profile\Profiles;

class MetadataProfileBuilder extends AbstractProfileBuilder
{
    protected function getProfileId(): string
    {
        return Profiles::METADATA;
    }

    protected function getProfileRole(): string
    {
        return ProfileContext::ROLE_NONE;
    }

    protected function getActionBuilder(): MetadataActionBuilder
    {
        return new MetadataActionBuilder($this->container);
    }
}
