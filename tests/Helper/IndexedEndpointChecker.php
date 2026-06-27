<?php

namespace Tests\Helper;

use LightSaml\Model\Metadata\IndexedEndpoint;
use Tests\BaseTestCase;

class IndexedEndpointChecker
{
    public static function check(BaseTestCase $test, string $binding, string $location, int $index, ?bool $isDefault, ?IndexedEndpoint $svc = null): void
    {
        EndpointChecker::check($test, $binding, $location, $svc);
        $test->assertEquals($index, $svc->getIndex());
        $test->assertEquals($isDefault, $svc->getIsDefaultBool());
    }
}
