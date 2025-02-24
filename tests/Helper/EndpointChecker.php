<?php

namespace Tests\Helper;

use LightSaml\Model\Metadata\Endpoint;
use Tests\BaseTestCase;

class EndpointChecker
{
    public static function check(BaseTestCase $test, $binding, $location, ?Endpoint $svc = null)
    {
        $test->assertNotNull($svc);
        $test->assertEquals($binding, $svc->getBinding());
        $test->assertEquals($location, $svc->getLocation());
    }
}
