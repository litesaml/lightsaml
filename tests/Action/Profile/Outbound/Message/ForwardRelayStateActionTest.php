<?php

namespace Tests\Action\Profile\Outbound\Message;

use LightSaml\Action\Profile\Outbound\Message\ForwardRelayStateAction;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Model\Protocol\AuthnRequest;
use LightSaml\Model\Protocol\Response;
use LightSaml\Profile\Profiles;
use Tests\BaseTestCase;

class ForwardRelayStateActionTest extends BaseTestCase
{
    public function test_constructs_with_logger()
    {
        new ForwardRelayStateAction($this->getLoggerMock());
        $this->assertTrue(true);
    }

    public function test_does_nothing_if_no_inbound_message()
    {
        $action = new ForwardRelayStateAction($this->getLoggerMock());

        $context = new ProfileContext(Profiles::SSO_IDP_RECEIVE_AUTHN_REQUEST, ProfileContext::ROLE_IDP);

        $action->execute($context);

        $this->assertTrue(true);
    }

    public function test_sets_relat_state_from_inbound_to_outbound_message()
    {
        $action = new ForwardRelayStateAction($this->getLoggerMock());

        $context = new ProfileContext(Profiles::SSO_IDP_RECEIVE_AUTHN_REQUEST, ProfileContext::ROLE_IDP);
        $context->getInboundContext()->setMessage($inboundMessage = new AuthnRequest());
        $context->getOutboundContext()->setMessage($outboundMessage = new Response());

        $inboundMessage->setRelayState($relayState = '123');

        $action->execute($context);

        $this->assertEquals($relayState, $context->getOutboundMessage()->getRelayState());
    }
}
