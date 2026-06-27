<?php

namespace LightSaml\Builder\Action\Profile\Metadata;

use LightSaml\Action\Profile\Entity\SerializeOwnEntityAction;
use LightSaml\Builder\Action\Profile\AbstractProfileActionBuilder;

class MetadataActionBuilder extends AbstractProfileActionBuilder
{
    protected function doInitialize(): void
    {
        $container = $this->buildContainer->getSystemContainer();
        $this->add(new SerializeOwnEntityAction(
            $container->getLogger(),
            $container->getResponseFactory(),
            $container->getStreamFactory()
        ), 100);
    }
}
