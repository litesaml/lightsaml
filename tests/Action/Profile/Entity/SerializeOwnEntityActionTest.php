<?php

namespace Tests\Action\Profile\Entity;

use LightSaml\Action\Profile\Entity\SerializeOwnEntityAction;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\Profile\Profiles;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\BaseTestCase;

class SerializeOwnEntityActionTest extends BaseTestCase
{
    public function test_constructs_with_logger()
    {
        new SerializeOwnEntityAction($this->getLoggerMock());
        $this->assertTrue(true);
    }

    public function test_creates_http_response_with_serialized_own_entity()
    {
        $loggerMock = $this->getLoggerMock();

        $action = new SerializeOwnEntityAction($loggerMock);

        $context = new ProfileContext(Profiles::METADATA, ProfileContext::ROLE_IDP);
        $context->getOwnEntityContext()->setEntityDescriptor($ownEntityDescriptor = new EntityDescriptor($myEntityId = 'http://localhost/myself'));
        $context->getHttpRequestContext()->setRequest($httpRequest = new Request());

        $httpRequest->headers->add(['Accept' => $contextType = 'application/samlmetadata+xml']);

        $action->execute($context);

        /** @var Response $response */
        $response = $context->getHttpResponseContext()->getResponse();
        $this->assertNotNull($response);
        $this->assertEquals($contextType, $response->headers->get('Content-Type'));

        $expectedContent = <<<EOT
<?xml version="1.0"?>
<EntityDescriptor xmlns="urn:oasis:names:tc:SAML:2.0:metadata" entityID="http://localhost/myself"/>
EOT;
        $expectedContent = trim(str_replace("\r", '', $expectedContent));

        $this->assertEquals($expectedContent, trim(str_replace("\r", '', $response->getContent())));
    }
}
