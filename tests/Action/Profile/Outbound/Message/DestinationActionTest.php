<?php

namespace Tests\Action\Profile\Outbound\Message;

use LightSaml\Action\Profile\Outbound\Message\DestinationAction;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Model\Metadata\SingleSignOnService;
use LightSaml\Model\Protocol\AuthnRequest;
use LightSaml\Profile\Profiles;
use Tests\BaseTestCase;

class DestinationActionTest extends BaseTestCase
{
    public function test_constructs_with_logger()
    {
        new DestinationAction($this->getLoggerMock());
        $this->assertTrue(true);
    }

    public function test_sets_outbounding_message_destination_to_endpoint_context_value()
    {
        $action = new DestinationAction($this->getLoggerMock());

        $context = new ProfileContext(Profiles::SSO_IDP_RECEIVE_AUTHN_REQUEST, ProfileContext::ROLE_IDP);
        $context->getOutboundContext()->setMessage($message = new AuthnRequest());

        $context->getEndpointContext()->setEndpoint($endpoint = new SingleSignOnService());
        $endpoint->setLocation($location = 'http://idp.com/login');

        $action->execute($context);

        $this->assertEquals($location, $message->getDestination());
    }
}
