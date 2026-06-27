<?php

namespace Tests\Helper;

use LightSaml\Model\Metadata\Organization;
use Tests\BaseTestCase;

class OrganizationChecker
{
    public static function check(BaseTestCase $test, string $name, string $display, string $url, ?Organization $organization = null): void
    {
        $test->assertNotNull($organization);
        $test->assertEquals($name, $organization->getOrganizationName());
        $test->assertEquals($display, $organization->getOrganizationDisplayName());
        $test->assertEquals($url, $organization->getOrganizationURL());
    }
}
