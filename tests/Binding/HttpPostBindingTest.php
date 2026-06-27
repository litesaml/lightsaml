<?php

namespace Tests\Binding;

use DOMDocument;
use DOMElement;
use DOMXPath;
use LightSaml\Binding\BindingFactory;
use LightSaml\Binding\HttpPostBinding;
use LightSaml\Context\Profile\MessageContext;
use LightSaml\Error\LightSamlBindingException;
use LightSaml\Error\LightSamlMissingFactoryException;
use LightSaml\Model\Protocol\Response;
use LightSaml\SamlConstants;
use Nyholm\Psr7\Factory\Psr17Factory;
use Tests\BaseTestCase;

class HttpPostBindingTest extends BaseTestCase
{
    public function test_send_throws_when_factories_not_set(): void
    {
        $this->expectException(LightSamlMissingFactoryException::class);
        $this->expectExceptionMessage('ResponseFactory and StreamFactory must be provided to use send()');

        $binding = new HttpPostBinding();
        $context = new MessageContext();
        $context->setMessage(new Response());

        $binding->send($context);
    }

    public function test_receive_throws_when_no_message(): void
    {
        $this->expectExceptionMessage("Missing SAMLRequest or SAMLResponse parameter");
        $this->expectException(LightSamlBindingException::class);
        $factory = new Psr17Factory();
        $request = $factory->createServerRequest('POST', '/');

        $binding = new HttpPostBinding();

        $messageContext = new MessageContext();

        $binding->receive($request, $messageContext);
    }

    public function test_relay_state_is_included_in_http_post(): void
    {
        $expectedRelayState = 'some_relay_state';

        $samlResponse = new Response();
        $samlResponse->setRelayState($expectedRelayState);

        $messageContext = new MessageContext();
        $messageContext->setMessage($samlResponse);

        $this->assertEquals($expectedRelayState, $messageContext->getMessage()->getRelayState());

        $psr17 = new Psr17Factory();
        $bindingFactory = new BindingFactory(null, $psr17, $psr17);
        $binding = $bindingFactory->create(SamlConstants::BINDING_SAML2_HTTP_POST);

        $httpResponse = $binding->send($messageContext);

        $html = (string) $httpResponse->getBody();

        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        $relayStateInput = $xpath->query('//input[@name="RelayState"]');

        $this->assertEquals(1, $relayStateInput->count());
        $node = $relayStateInput->item(0);
        assert($node instanceof DOMElement);
        $actualRelayState = $node->getAttribute('value');
        $this->assertEquals($expectedRelayState, $actualRelayState);
    }
}
