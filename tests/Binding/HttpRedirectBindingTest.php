<?php

namespace Tests\Binding;

use LightSaml\Binding\HttpRedirectBinding;
use LightSaml\Context\Profile\MessageContext;
use LightSaml\Error\LightSamlBindingException;
use LightSaml\Error\LightSamlMissingFactoryException;
use LightSaml\Model\Protocol\AuthnRequest;
use Nyholm\Psr7\Factory\Psr17Factory;
use Tests\BaseTestCase;

class HttpRedirectBindingTest extends BaseTestCase
{
    public function test_send_throws_when_factory_not_set(): void
    {
        $this->expectException(LightSamlMissingFactoryException::class);
        $this->expectExceptionMessage('ResponseFactory must be provided to use send()');

        $binding = new HttpRedirectBinding();
        $context = new MessageContext();
        $context->setMessage((new AuthnRequest())->setDestination('https://idp.example.com/sso'));

        $binding->send($context);
    }

    public function test__receive_throws_when_no_message(): void
    {
        $this->expectExceptionMessage("Missing SAMLRequest or SAMLResponse parameter");
        $this->expectException(LightSamlBindingException::class);
        $factory = new Psr17Factory();
        $request = $factory->createServerRequest('GET', '/');

        $binding = new HttpRedirectBinding();

        $messageContext = new MessageContext();

        $binding->receive($request, $messageContext);
    }
}
