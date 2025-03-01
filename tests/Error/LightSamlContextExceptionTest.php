<?php

namespace Tests\Error;

use LightSaml\Error\LightSamlContextException;
use Tests\BaseTestCase;

class LightSamlContextExceptionTest extends BaseTestCase
{
    public function test_returns_context_its_constructed_with()
    {
        $context = $this->getProfileContext();

        $exception = new LightSamlContextException($context, 'message');

        $this->assertSame($context, $exception->getContext());
    }
}
