<?php

namespace Tests\Context\Profile;

use LightSaml\Context\Profile\MessageContext;
use LightSaml\Model\Protocol\AuthnRequest;
use LightSaml\Model\Protocol\LogoutRequest;
use LightSaml\Model\Protocol\LogoutResponse;
use LightSaml\Model\Protocol\Response;
use LightSaml\Model\Protocol\SamlMessage;
use Tests\BaseTestCase;

class MessageContextTest extends BaseTestCase
{
    public static function message_as_concrete_type_provider()
    {
        return [
            ['asAuthnRequest', true, new AuthnRequest()],
            ['asAuthnRequest', false, new Response()],

            ['asLogoutRequest', true, new LogoutRequest()],
            ['asLogoutRequest', false, new Response()],

            ['asResponse', true, new Response()],
            ['asResponse', false, new LogoutRequest()],

            ['asLogoutResponse', true, new LogoutResponse()],
            ['asLogoutResponse', false, new LogoutRequest()],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('message_as_concrete_type_provider')]
    public function test_message_as_concrete_type($method, $hasValue, ?SamlMessage $message = null)
    {
        $context = new MessageContext();
        if ($message instanceof \LightSaml\Model\Protocol\SamlMessage) {
            $context->setMessage($message);
        }

        $actualValue = $context->{$method}();

        if ($hasValue) {
            $this->assertSame($message, $actualValue);
        } else {
            $this->assertNull($actualValue);
        }
    }
}
