<?php

namespace LightSaml\Tests\Binding;

use LightSaml\Binding\HttpRedirectBinding;
use LightSaml\Context\Profile\MessageContext;
use LightSaml\Tests\BaseTestCase;

class HttpRedirectBindingTest extends BaseTestCase
{
    public function test__receive_throws_when_no_message()
    {
        $this->expectExceptionMessage("Missing SAMLRequest or SAMLResponse parameter");
        $this->expectException(\LightSaml\Error\LightSamlBindingException::class);
        $request = $this->getRequestMock('GET');

        $binding = new HttpRedirectBinding();

        $messageContext = new MessageContext();

        $binding->receive($request, $messageContext);
    }
}
