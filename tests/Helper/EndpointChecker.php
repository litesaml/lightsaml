<?php

namespace Tests\Helper;

use LightSaml\Model\Metadata\Endpoint;
use Tests\BaseTestCase;

class EndpointChecker
{
    public static function check(BaseTestCase $test, string $binding, string $location, ?Endpoint $svc = null): void
    {
        $test->assertNotNull($svc);
        $test->assertEquals($binding, $svc->getBinding());
        $test->assertEquals($location, $svc->getLocation());
    }
}
