<?php

namespace Tests\Credential\Context;

use LightSaml\Credential\Context\MetadataCredentialContext;
use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\Model\Metadata\KeyDescriptor;
use LightSaml\Model\Metadata\RoleDescriptor;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\BaseTestCase;

class MetadataCredentialContextTest extends BaseTestCase
{
    public function test_returns_objects_set_on_construct()
    {
        $context = new MetadataCredentialContext(
            $keyDescriptor = $this->getKeyDescriptorMock(),
            $roleDescriptor = $this->getRoleDescriptorMock(),
            $entityDescriptor = $this->getEntityDescriptorMock()
        );

        $this->assertSame($keyDescriptor, $context->getKeyDescriptor());
        $this->assertSame($roleDescriptor, $context->getRoleDescriptor());
        $this->assertSame($entityDescriptor, $context->getEntityDescriptor());
    }

    /**
     * @return MockObject|KeyDescriptor
     */
    private function getKeyDescriptorMock()
    {
        return $this->getMockBuilder(KeyDescriptor::class)->getMock();
    }

    /**
     * @return MockObject|RoleDescriptor
     */
    private function getRoleDescriptorMock()
    {
        return $this->getMockBuilder(RoleDescriptor::class)->getMock();
    }

    /**
     * @return MockObject|EntityDescriptor
     */
    private function getEntityDescriptorMock()
    {
        return $this->getMockBuilder(EntityDescriptor::class)->getMock();
    }
}
