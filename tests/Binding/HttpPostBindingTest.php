<?php

namespace Tests\Binding;

use DOMDocument;
use DOMXPath;
use LightSaml\Binding\BindingFactory;
use LightSaml\Binding\HttpPostBinding;
use LightSaml\Context\Profile\MessageContext;
use LightSaml\Error\LightSamlBindingException;
use LightSaml\Model\Protocol\Response;
use LightSaml\SamlConstants;
use Symfony\Component\HttpFoundation\Request;
use Tests\BaseTestCase;

class HttpPostBindingTest extends BaseTestCase
{
    public function test_receive_throws_when_no_message()
    {
        $this->expectExceptionMessage("Missing SAMLRequest or SAMLResponse parameter");
        $this->expectException(LightSamlBindingException::class);
        $request = new Request();

        $binding = new HttpPostBinding();

        $messageContext = new MessageContext();

        $binding->receive($request, $messageContext);
    }

    public function test_relay_state_is_included_in_http_post()
    {
        $expectedRelayState = 'some_relay_state';

        $samlResponse = new Response();
        $samlResponse->setRelayState($expectedRelayState);

        $messageContext = new MessageContext();
        $messageContext->setMessage($samlResponse);

        $this->assertEquals($expectedRelayState, $messageContext->getMessage()->getRelayState());

        $bindingFactory = new BindingFactory();
        $binding = $bindingFactory->create(SamlConstants::BINDING_SAML2_HTTP_POST);

        $httpResponse = $binding->send($messageContext);

        $html = $httpResponse->getContent();

        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        $relayStateInput = $xpath->query('//input[@name="RelayState"]');

        $this->assertEquals(1, $relayStateInput->count());
        $actualRelayState = $relayStateInput->item(0)->getAttribute('value');
        $this->assertEquals($expectedRelayState, $actualRelayState);
    }
}
