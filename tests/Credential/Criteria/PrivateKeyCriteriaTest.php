<?php

namespace Tests\Credential\Criteria;

use LightSaml\Credential\Criteria\PrivateKeyCriteria;
use LightSaml\Credential\Criteria\TrustCriteriaInterface;
use Tests\BaseTestCase;

class PrivateKeyCriteriaTest extends BaseTestCase
{
    public function test_implements_trust_criteria_interface()
    {
        $this->assertInstanceOf(TrustCriteriaInterface::class, new PrivateKeyCriteria());
    }
}
