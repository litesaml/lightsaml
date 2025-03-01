<?php

namespace Tests\Store\EntityDescriptor;

use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\Store\EntityDescriptor\CompositeEntityDescriptorStore;
use Tests\BaseTestCase;

class CompositeEntityDescriptorStoreTest extends BaseTestCase
{
    public function test_constructs_without_arguments()
    {
        new CompositeEntityDescriptorStore();
        $this->assertTrue(true);
    }

    public function test_constructs_with_array_of_entity_descriptor_stores()
    {
        new CompositeEntityDescriptorStore([
            $this->getEntityDescriptorStoreMock(),
            $this->getEntityDescriptorStoreMock(),
        ]);
        $this->assertTrue(true);
    }

    public function test_entity_descriptor_store_can_be_added()
    {
        $composite = new CompositeEntityDescriptorStore();
        $composite->add($this->getEntityDescriptorStoreMock());
        $this->assertTrue(true);
    }

    public function test_get_returns_value_given_by_child_store()
    {
        $composite = new CompositeEntityDescriptorStore([
            $child1 = $this->getEntityDescriptorStoreMock(),
            $child2 = $this->getEntityDescriptorStoreMock(),
            $child3 = $this->getEntityDescriptorStoreMock(),
        ]);

        $entityId = 'http://entity.id';
        $child1->expects($this->once())->method('get')->with($entityId)->willReturn(null);
        $child2->expects($this->once())->method('get')->with($entityId)->willReturn($expected = new EntityDescriptor());
        $child3->expects($this->never())->method('get');

        $actual = $composite->get($entityId);
        $this->assertSame($expected, $actual);
    }

    public function test_has_return_true_if_any_child_returns_true()
    {
        $composite = new CompositeEntityDescriptorStore([
            $child1 = $this->getEntityDescriptorStoreMock(),
            $child2 = $this->getEntityDescriptorStoreMock(),
            $child3 = $this->getEntityDescriptorStoreMock(),
        ]);

        $entityId = 'http://entity.id';
        $child1->expects($this->once())->method('has')->with($entityId)->willReturn(false);
        $child2->expects($this->once())->method('has')->with($entityId)->willReturn(true);
        $child3->expects($this->never())->method('has');

        $this->assertTrue($composite->has($entityId));
    }

    public function test_all_returns_union_of_all_children_results()
    {
        $composite = new CompositeEntityDescriptorStore([
            $child1 = $this->getEntityDescriptorStoreMock(),
            $child2 = $this->getEntityDescriptorStoreMock(),
            $child3 = $this->getEntityDescriptorStoreMock(),
        ]);

        $child1->expects($this->once())->method('all')->willReturn([$ed1 = new EntityDescriptor()]);
        $child2->expects($this->once())->method('all')->willReturn([$ed2 = new EntityDescriptor(), $ed3 = new EntityDescriptor()]);
        $child3->expects($this->once())->method('all')->willReturn([]);

        $all = $composite->all();

        $this->assertCount(3, $all);
        $this->assertSame($ed1, $all[0]);
        $this->assertSame($ed2, $all[1]);
        $this->assertSame($ed3, $all[2]);
    }
}
