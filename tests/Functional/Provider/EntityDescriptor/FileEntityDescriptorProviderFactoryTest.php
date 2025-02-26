<?php

namespace Tests\Functional\Provider\EntityDescriptor;

use LightSaml\Provider\EntityDescriptor\FileEntityDescriptorProviderFactory;
use Tests\BaseTestCase;

class FileEntityDescriptorProviderFactoryTest extends BaseTestCase
{
    public function test_loads_entity_descriptor_from_file()
    {
        $provider = FileEntityDescriptorProviderFactory::fromEntityDescriptorFile(
            __DIR__ . '/../../../resources/idp-ed.xml'
        );

        $entityDescriptor = $provider->get();

        $this->assertEquals('_127800fe-39ac-46ad-b073-6fb6106797a0', $entityDescriptor->getID());
    }

    public function test_loads_entities_descriptor_from_file()
    {
        $provider = FileEntityDescriptorProviderFactory::fromEntitiesDescriptorFile(
            __DIR__ . '/../../../resources/testshib-providers.xml',
            'https://idp.testshib.org/idp/shibboleth'
        );

        $entityDescriptor = $provider->get();

        $this->assertEquals('https://idp.testshib.org/idp/shibboleth', $entityDescriptor->getEntityID());
    }
}
