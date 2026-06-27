<?php

namespace Tests\Action\Profile\Entity;

use LightSaml\Action\Profile\Entity\SerializeOwnEntityAction;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\Profile\Profiles;
use Nyholm\Psr7\Factory\Psr17Factory;
use Tests\BaseTestCase;

class SerializeOwnEntityActionTest extends BaseTestCase
{
    public function test_constructs_with_logger(): void
    {
        $factory = new Psr17Factory();
        new SerializeOwnEntityAction($this->getLoggerMock(), $factory, $factory);
        $this->assertTrue(true);
    }

    public function test_creates_http_response_with_serialized_own_entity(): void
    {
        $loggerMock = $this->getLoggerMock();

        $psr17 = new Psr17Factory();
        $action = new SerializeOwnEntityAction($loggerMock, $psr17, $psr17);

        $context = new ProfileContext(Profiles::METADATA, ProfileContext::ROLE_IDP);
        $context->getOwnEntityContext()->setEntityDescriptor($ownEntityDescriptor = new EntityDescriptor($myEntityId = 'http://localhost/myself'));

        $httpRequest = $psr17->createServerRequest('GET', '/')->withHeader('Accept', $contextType = 'application/samlmetadata+xml');
        $context->getHttpRequestContext()->setRequest($httpRequest);

        $action->execute($context);

        $response = $context->getHttpResponseContext()->getResponse();
        $this->assertNotNull($response);
        $this->assertEquals($contextType, $response->getHeaderLine('Content-Type'));

        $expectedContent = <<<EOT
<?xml version="1.0"?>
<EntityDescriptor xmlns="urn:oasis:names:tc:SAML:2.0:metadata" entityID="http://localhost/myself"/>
EOT;
        $expectedContent = trim(str_replace("\r", '', $expectedContent));

        $this->assertEquals($expectedContent, trim(str_replace("\r", '', (string) $response->getBody())));
    }

    public function test_defaults_to_text_xml_when_accept_header_is_empty(): void
    {
        $psr17 = new Psr17Factory();
        $action = new SerializeOwnEntityAction($this->getLoggerMock(), $psr17, $psr17);

        $context = new ProfileContext(Profiles::METADATA, ProfileContext::ROLE_IDP);
        $context->getOwnEntityContext()->setEntityDescriptor(new EntityDescriptor('http://localhost/myself'));
        $context->getHttpRequestContext()->setRequest($psr17->createServerRequest('GET', '/'));

        $action->execute($context);

        $this->assertSame('text/xml', $context->getHttpResponseContext()->getResponse()->getHeaderLine('Content-Type'));
    }

    public function test_defaults_to_text_xml_when_accept_header_has_no_supported_type(): void
    {
        $psr17 = new Psr17Factory();
        $action = new SerializeOwnEntityAction($this->getLoggerMock(), $psr17, $psr17);

        $context = new ProfileContext(Profiles::METADATA, ProfileContext::ROLE_IDP);
        $context->getOwnEntityContext()->setEntityDescriptor(new EntityDescriptor('http://localhost/myself'));
        $context->getHttpRequestContext()->setRequest(
            $psr17->createServerRequest('GET', '/')->withHeader('Accept', 'text/html, application/json')
        );

        $action->execute($context);

        $this->assertSame('text/xml', $context->getHttpResponseContext()->getResponse()->getHeaderLine('Content-Type'));
    }

    public function test_content_type_priority_follows_supported_types_order_not_accept_order(): void
    {
        $psr17 = new Psr17Factory();
        $action = new SerializeOwnEntityAction($this->getLoggerMock(), $psr17, $psr17);

        $context = new ProfileContext(Profiles::METADATA, ProfileContext::ROLE_IDP);
        $context->getOwnEntityContext()->setEntityDescriptor(new EntityDescriptor('http://localhost/myself'));
        // Accept header lists application/xml first, but application/samlmetadata+xml has higher
        // priority in supportedContextTypes, so it wins
        $context->getHttpRequestContext()->setRequest(
            $psr17->createServerRequest('GET', '/')->withHeader('Accept', 'application/xml, application/samlmetadata+xml')
        );

        $action->execute($context);

        $this->assertSame('application/samlmetadata+xml', $context->getHttpResponseContext()->getResponse()->getHeaderLine('Content-Type'));
    }

    public function test_strips_quality_parameter_from_accept_header(): void
    {
        $psr17 = new Psr17Factory();
        $action = new SerializeOwnEntityAction($this->getLoggerMock(), $psr17, $psr17);

        $context = new ProfileContext(Profiles::METADATA, ProfileContext::ROLE_IDP);
        $context->getOwnEntityContext()->setEntityDescriptor(new EntityDescriptor('http://localhost/myself'));
        $context->getHttpRequestContext()->setRequest(
            $psr17->createServerRequest('GET', '/')->withHeader('Accept', 'application/samlmetadata+xml;q=0.9')
        );

        $action->execute($context);

        $this->assertSame('application/samlmetadata+xml', $context->getHttpResponseContext()->getResponse()->getHeaderLine('Content-Type'));
    }
}
