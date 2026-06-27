<?php

namespace Tests\Store\TrustOptions;

use LightSaml\Meta\TrustOptions\TrustOptions;
use LightSaml\Store\TrustOptions\CompositeTrustOptionsStore;
use LightSaml\Store\TrustOptions\TrustOptionsStoreInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\BaseTestCase;

class CompositeTrustOptionsStoreTest extends BaseTestCase
{
    public function test_constructs_without_arguments(): void
    {
        new CompositeTrustOptionsStore();
        $this->assertTrue(true);
    }

    public function test_constructs_wit_array_of_stores(): void
    {
        new CompositeTrustOptionsStore([$this->getTrustOptionsStoreMock(), $this->getTrustOptionsStoreMock()]);
        $this->assertTrue(true);
    }

    public function test_can_add_stores(): void
    {
        $composite = new CompositeTrustOptionsStore();
        $composite->add($this->getTrustOptionsStoreMock());
        $this->assertTrue(true);
    }

    public function test_get_calls_each_store(): void
    {
        $expectedEntityId = 'id';
        $composite = new CompositeTrustOptionsStore();
        $store = $this->getTrustOptionsStoreMock();
        $store->expects($this->once())
            ->method('get')
            ->with($expectedEntityId)
            ->willReturn(null);
        $composite->add($store);

        $result = $composite->get($expectedEntityId);

        $this->assertNull($result);
    }

    public function test_get_returns_first_result(): void
    {
        $expectedEntityId = 'id';
        $expectedTrustOptions = new TrustOptions();
        $composite = new CompositeTrustOptionsStore();

        $composite->add($this->getTrustOptionsStoreMock());

        $store = $this->getTrustOptionsStoreMock();
        $store->expects($this->once())
            ->method('get')
            ->with($expectedEntityId)
            ->willReturn($expectedTrustOptions);
        $composite->add($store);

        $composite->add($this->getTrustOptionsStoreMock());

        $result = $composite->get($expectedEntityId);

        $this->assertSame($expectedTrustOptions, $result);
    }

    public function test_has_calls_each_store(): void
    {
        $expectedEntityId = 'id';
        $composite = new CompositeTrustOptionsStore();
        $store = $this->getTrustOptionsStoreMock();
        $store->expects($this->once())
            ->method('has')
            ->with($expectedEntityId)
            ->willReturn(false);
        $composite->add($store);

        $result = $composite->has($expectedEntityId);

        $this->assertFalse($result);
    }

    public function test_has_returns_true_on_first_true(): void
    {
        $expectedEntityId = 'id';
        $composite = new CompositeTrustOptionsStore();

        $composite->add($this->getTrustOptionsStoreMock());

        $store = $this->getTrustOptionsStoreMock();
        $store->expects($this->once())
            ->method('has')
            ->with($expectedEntityId)
            ->willReturn(true);
        $composite->add($store);

        $composite->add($this->getTrustOptionsStoreMock());

        $result = $composite->has($expectedEntityId);

        $this->assertTrue($result);
    }

    /**
     * @return MockObject|TrustOptionsStoreInterface
     */
    private function getTrustOptionsStoreMock(): MockObject
    {
        return $this->getMockBuilder(TrustOptionsStoreInterface::class)->getMock();
    }
}
