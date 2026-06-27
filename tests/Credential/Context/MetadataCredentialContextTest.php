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
    public function test_returns_objects_set_on_construct(): void
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
     * @return KeyDescriptor&MockObject
     */
    private function getKeyDescriptorMock(): MockObject
    {
        return $this->getMockBuilder(KeyDescriptor::class)->getMock();
    }

    /**
     * @return RoleDescriptor&MockObject
     */
    private function getRoleDescriptorMock(): MockObject
    {
        return $this->getMockBuilder(RoleDescriptor::class)->getMock();
    }

    /**
     * @return EntityDescriptor&MockObject
     */
    private function getEntityDescriptorMock(): MockObject
    {
        return $this->getMockBuilder(EntityDescriptor::class)->getMock();
    }
}
