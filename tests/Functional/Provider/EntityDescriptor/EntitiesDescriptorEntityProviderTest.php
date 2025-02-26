<?php

namespace Tests\Functional\Provider\EntityDescriptor;

use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\Provider\EntitiesDescriptor\FileEntitiesDescriptorProvider;
use LightSaml\Provider\EntityDescriptor\EntitiesDescriptorEntityProvider;
use Tests\BaseTestCase;

class EntitiesDescriptorEntityProviderTest extends BaseTestCase
{
    public function test___provides_by_specified_entity_id()
    {
        $entitiesProvider = new FileEntitiesDescriptorProvider(
            __DIR__ . '/../../../resources/testshib-providers.xml'
        );

        $provider = new EntitiesDescriptorEntityProvider(
            $entitiesProvider,
            $expectedEntityId = 'https://idp.testshib.org/idp/shibboleth'
        );
        $entityDescriptor = $provider->get();
        $this->assertInstanceOf(EntityDescriptor::class, $entityDescriptor);
        $this->assertEquals($expectedEntityId, $entityDescriptor->getEntityID());

        $provider = new EntitiesDescriptorEntityProvider(
            $entitiesProvider,
            $expectedEntityId = 'https://sp.testshib.org/shibboleth-sp'
        );
        $entityDescriptor = $provider->get();
        $this->assertInstanceOf(EntityDescriptor::class, $entityDescriptor);
        $this->assertEquals($expectedEntityId, $entityDescriptor->getEntityID());
    }
}
