<?php

namespace Tests\Functional\Model\Metadata;

use LightSaml\Model\Metadata\EntitiesDescriptor;
use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\Model\Metadata\Metadata;
use Tests\BaseTestCase;

class MetadataTest extends BaseTestCase
{
    public function test_loads_from_entity_descriptor()
    {
        $ed = Metadata::fromFile(__DIR__ . '/../../../resources/idp2-ed.xml');
        $this->assertInstanceOf(EntityDescriptor::class, $ed);
        $this->assertEquals('https://B1.bead.loc/adfs/services/trust', $ed->getEntityID());
    }

    public function test_loads_from_entities_descriptor()
    {
        $eds = Metadata::fromFile(__DIR__ . '/../../../resources/testshib-providers.xml');
        $this->assertInstanceOf(EntitiesDescriptor::class, $eds);
        $this->assertCount(2, $eds->getAllEntityDescriptors());
    }
}
