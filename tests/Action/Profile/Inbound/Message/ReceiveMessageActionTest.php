<?php

namespace Tests\Action\Profile\Inbound\Message;

use LightSaml\Action\Profile\Inbound\Message\ReceiveMessageAction;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Error\LightSamlBindingException;
use LightSaml\Profile\Profiles;
use LightSaml\SamlConstants;
use Nyholm\Psr7\Factory\Psr17Factory;
use Tests\BaseTestCase;

class ReceiveMessageActionTest extends BaseTestCase
{
    public function test_constructs_with_logger_and_binding_factory(): void
    {
        new ReceiveMessageAction($this->getLoggerMock(), $this->getBindingFactoryMock());
        $this->assertTrue(true);
    }

    public function test_throws_on_invalid_binding(): void
    {
        $this->expectExceptionMessage("Unable to resolve binding type, invalid or unsupported http request");
        $this->expectException(LightSamlBindingException::class);
        $action = new ReceiveMessageAction($logger = $this->getLoggerMock(), $bindingFactory = $this->getBindingFactoryMock());

        $context = new ProfileContext(Profiles::SSO_SP_SEND_AUTHN_REQUEST, ProfileContext::ROLE_SP);

        $psr17 = new Psr17Factory();
        $context->getHttpRequestContext()->setRequest($request = $psr17->createServerRequest('GET', '/'));
        $bindingFactory->expects($this->once())
            ->method('detectBindingType')
            ->with($request)
            ->willReturn(null)
        ;
        $logger->expects($this->once())
            ->method('critical')
            ->with('Unable to resolve binding type, invalid or unsupported http request', $this->isType('array'))
        ;

        $action->execute($context);
    }

    public function test_receives_message(): void
    {
        $action = new ReceiveMessageAction($logger = $this->getLoggerMock(), $bindingFactory = $this->getBindingFactoryMock());

        $context = new ProfileContext(Profiles::SSO_SP_SEND_AUTHN_REQUEST, ProfileContext::ROLE_SP);
        $psr17 = new Psr17Factory();
        $context->getHttpRequestContext()->setRequest($request = $psr17->createServerRequest('GET', '/'));

        $binding = $this->getBindingMock();

        $bindingFactory->expects($this->once())
            ->method('detectBindingType')
            ->with($request)
            ->willReturn($bindingType = SamlConstants::BINDING_SAML2_HTTP_POST)
        ;
        $logger->expects($this->once())
            ->method('debug')
            ->with('Detected binding type: urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST', $this->isType('array'))
        ;
        $bindingFactory->expects($this->once())
            ->method('create')
            ->with($bindingType)
            ->willReturn($binding)
        ;
        $binding->expects($this->once())
            ->method('receive')
            ->with($request, $context->getInboundContext())
        ;

        $action->execute($context);

        $this->assertEquals($bindingType, $context->getInboundContext()->getBindingType());
    }
}
