<?php

namespace Tests\Action\Profile\Inbound\Message;

use LightSaml\Action\Profile\Inbound\Message\DestinationValidatorAuthnRequestAction;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Criteria\CriteriaSet;
use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\Model\Metadata\SingleSignOnService;
use LightSaml\Model\Protocol\AuthnRequest;
use LightSaml\Profile\Profiles;
use LightSaml\Resolver\Endpoint\Criteria\ServiceTypeCriteria;
use Tests\BaseTestCase;

class DestinationValidatorAuthnRequestActionTest extends BaseTestCase
{
    public function test_creates_sso_service_type_criteria(): void
    {
        $endpointResolverMock = $this->getEndpointResolverMock();

        $action = new DestinationValidatorAuthnRequestAction($this->getLoggerMock(), $endpointResolverMock);

        $context = $this->buildContext(ProfileContext::ROLE_IDP, 'http://localhost');

        $endpointResolverMock->expects($this->once())
            ->method('resolve')
            ->willReturnCallback(function (CriteriaSet $criteriaSet, array $endpoints): array {
                $this->assertTrue($criteriaSet->has(ServiceTypeCriteria::class));
                $arr = $criteriaSet->get(ServiceTypeCriteria::class);
                $this->assertCount(1, $arr);
                /** @var ServiceTypeCriteria $criteria */
                $criteria = $arr[0];
                $this->assertEquals(SingleSignOnService::class, $criteria->getServiceType());

                return [1];
            });

        $action->execute($context);
    }

    
    private function buildContext(string $ownRole, string $destination): \LightSaml\Context\Profile\ProfileContext
    {
        $context = new ProfileContext(Profiles::SSO_IDP_RECEIVE_AUTHN_REQUEST, $ownRole);
        $context->getInboundContext()->setMessage(new AuthnRequest());
        if ($destination !== '' && $destination !== '0') {
            $context->getInboundMessage()->setDestination($destination);
        }

        $context->getOwnEntityContext()->setEntityDescriptor(new EntityDescriptor());

        return $context;
    }
}
