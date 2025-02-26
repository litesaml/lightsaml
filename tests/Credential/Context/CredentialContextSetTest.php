<?php

namespace Tests\Credential\Context;

use InvalidArgumentException;
use LightSaml\Credential\Context\CredentialContextSet;
use LightSaml\Credential\Context\MetadataCredentialContext;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Tests\BaseTestCase;

class CredentialContextSetTest extends BaseTestCase
{
    public function test_metadata_context_is_null_upon_creation()
    {
        $context = new CredentialContextSet();

        $this->assertNull($context->get(MetadataCredentialContext::class));
    }

    public function test_returns_set_metadata_context()
    {
        $context = new CredentialContextSet([$metadataContextMock = $this->getMetadataContextMock()]);

        $this->assertSame($metadataContextMock, $context->get(MetadataCredentialContext::class));
    }

    public function test_returns_all_contexts()
    {
        $context = new CredentialContextSet($expected = [$this->getMetadataContextMock(), $this->getMetadataContextMock()]);

        $all = $context->all();
        $this->assertCount(2, $all);

        $this->assertSame($expected[0], $all[0]);
        $this->assertSame($expected[1], $all[1]);
    }

    public function test_throws_invalid_argument_exception_if_constructed_with_non_credential_context_array()
    {
        $this->expectExceptionMessage("Expected CredentialContextInterface");
        $this->expectException(InvalidArgumentException::class);
        new CredentialContextSet([new stdClass()]);
    }

    /**
     * @return MockObject|MetadataCredentialContext
     */
    private function getMetadataContextMock()
    {
        return $this->getMockBuilder(MetadataCredentialContext::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
