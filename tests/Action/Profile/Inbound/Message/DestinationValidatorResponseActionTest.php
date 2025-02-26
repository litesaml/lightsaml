<?php

namespace Tests\Action\Profile\Inbound\Message;

use LightSaml\Action\Profile\Inbound\Message\DestinationValidatorResponseAction;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Criteria\CriteriaSet;
use LightSaml\Model\Metadata\AssertionConsumerService;
use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\Model\Protocol\AuthnRequest;
use LightSaml\Profile\Profiles;
use LightSaml\Resolver\Endpoint\Criteria\ServiceTypeCriteria;
use Tests\BaseTestCase;

class DestinationValidatorResponseActionTest extends BaseTestCase
{
    public function test_creates_acs_service_type_criteria()
    {
        $endpointResolverMock = $this->getEndpointResolverMock();

        $action = new DestinationValidatorResponseAction($this->getLoggerMock(), $endpointResolverMock);

        $context = $this->buildContext(ProfileContext::ROLE_IDP, 'http://localhost');

        $endpointResolverMock->expects($this->once())
            ->method('resolve')
            ->willReturnCallback(function (CriteriaSet $criteriaSet, array $endpoints) {
                $this->assertTrue($criteriaSet->has(ServiceTypeCriteria::class));
                $arr = $criteriaSet->get(ServiceTypeCriteria::class);
                $this->assertCount(1, $arr);
                /** @var ServiceTypeCriteria $criteria */
                $criteria = $arr[0];
                $this->assertEquals(AssertionConsumerService::class, $criteria->getServiceType());

                return true;
            });

        $action->execute($context);
    }

    /**
     * @param string $ownRole
     * @param string $destination
     *
     * @return ProfileContext
     */
    private function buildContext($ownRole, $destination)
    {
        $context = new ProfileContext(Profiles::SSO_IDP_RECEIVE_AUTHN_REQUEST, $ownRole);
        $context->getInboundContext()->setMessage(new AuthnRequest());
        if ($destination) {
            $context->getInboundMessage()->setDestination($destination);
        }

        $context->getOwnEntityContext()->setEntityDescriptor(new EntityDescriptor());

        return $context;
    }
}
