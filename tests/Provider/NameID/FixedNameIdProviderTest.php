<?php

namespace Tests\Provider\NameID;

use LightSaml\Model\Assertion\NameID;
use LightSaml\Provider\NameID\FixedNameIdProvider;
use Tests\BaseTestCase;

class FixedNameIdProviderTest extends BaseTestCase
{
    public function test_returns_given_name_id()
    {
        $provider = new FixedNameIdProvider($expected = new NameID());
        $this->assertSame($expected, $provider->getNameID($this->getProfileContext()));
    }
}
