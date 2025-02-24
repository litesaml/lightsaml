<?php

namespace Tests\Model\Assertion;

use LightSaml\Model\Assertion\AudienceRestriction;
use Tests\BaseTestCase;

class AudienceRestrictionTest extends BaseTestCase
{
    public function test_has_audience()
    {
        $audienceRestriction = new AudienceRestriction(['a', 'b', 'c']);
        $this->assertTrue($audienceRestriction->hasAudience('a'));
        $this->assertFalse($audienceRestriction->hasAudience('x'));
    }
}
