<?php

namespace Tests\Criteria;

use LightSaml\Criteria\CriteriaInterface;
use LightSaml\Criteria\CriteriaSet;
use LightSaml\Resolver\Endpoint\Criteria\IndexCriteria;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\BaseTestCase;

class CriteriaSetTest extends BaseTestCase
{
    public function test_add_all(): void
    {
        $criteriaSet = new CriteriaSet();
        $criteriaSet->addAll(new CriteriaSet([
            $criteria1 = $this->getCriteriaMock(),
            $criteria2 = $this->getCriteriaMock(),
        ]));

        $all = $criteriaSet->all();

        $this->assertCount(2, $all);
        $this->assertSame($criteria1, $all[0]);
        $this->assertSame($criteria2, $all[1]);
    }

    public function test_add_if_none(): void
    {
        $criteriaSet = new CriteriaSet();

        $criteriaSet->addIfNone(new IndexCriteria(1));
        $criteriaSet->addIfNone(new IndexCriteria(1));

        $all = $criteriaSet->all();

        $this->assertCount(1, $all);
    }

    public function test_add_if(): void
    {
        $criteriaSet = new CriteriaSet();

        $criteriaSet->addIf(false, function (): MockObject|CriteriaInterface {
            return $this->getCriteriaMock();

        });
        $criteriaSet->addIf(true, function (): MockObject|CriteriaInterface {
            return $this->getCriteriaMock();

        });

        $all = $criteriaSet->all();

        $this->assertCount(1, $all);
    }
}
