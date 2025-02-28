<?php

namespace Tests\Binding;

use LightSaml\Binding\HttpRedirectBinding;
use LightSaml\Context\Profile\MessageContext;
use LightSaml\Error\LightSamlBindingException;
use Symfony\Component\HttpFoundation\Request;
use Tests\BaseTestCase;

class HttpRedirectBindingTest extends BaseTestCase
{
    public function test__receive_throws_when_no_message()
    {
        $this->expectExceptionMessage("Missing SAMLRequest or SAMLResponse parameter");
        $this->expectException(LightSamlBindingException::class);
        $request = new Request();

        $binding = new HttpRedirectBinding();

        $messageContext = new MessageContext();

        $binding->receive($request, $messageContext);
    }
}
