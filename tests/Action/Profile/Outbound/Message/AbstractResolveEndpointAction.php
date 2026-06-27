<?php

namespace Tests\Action\Profile\Outbound\Message;

use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Criteria\CriteriaSet;
use LightSaml\Model\Metadata\Endpoint;
use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\Model\Protocol\SamlMessage;
use LightSaml\Profile\Profiles;
use LightSaml\Resolver\Endpoint\Criteria\BindingCriteria;
use LightSaml\Resolver\Endpoint\Criteria\DescriptorTypeCriteria;
use LightSaml\Resolver\Endpoint\Criteria\IndexCriteria;
use LightSaml\Resolver\Endpoint\Criteria\LocationCriteria;
use LightSaml\Resolver\Endpoint\Criteria\ServiceTypeCriteria;
use LightSaml\Resolver\Endpoint\EndpointResolverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Tests\BaseTestCase;

abstract class AbstractResolveEndpointAction extends BaseTestCase
{
    /** @var ResolveEndpointBaseActionTest|MockObject */
    protected $action;

    /** @var LoggerInterface|MockObject */
    protected $logger;

    /** @var  EndpointResolverInterface|MockObject */
    protected \PHPUnit\Framework\MockObject\MockObject $endpointResolver;

    /**
     *
     */
    protected function setUp(): void
    {
        $this->logger = $this->getLoggerMock();
        $this->endpointResolver = $this->getMockBuilder(EndpointResolverInterface::class)->getMock();
        $this->action = $this->createAction($this->logger, $this->endpointResolver);
    }

    
    abstract protected function createAction(LoggerInterface $logger, EndpointResolverInterface $endpointResolver);

    protected function setEndpointResolver(bool $shouldBeCalled, ?callable $callback)
    {
        if ($shouldBeCalled) {
            $this->endpointResolver->expects($this->once())
                ->method('resolve')
                ->willReturnCallback($callback);
        } else {
            $this->endpointResolver->expects($this->never())
                ->method('resolve');
        }
    }

    
    protected function createContext(
        string $ownRole = ProfileContext::ROLE_IDP,
        ?SamlMessage $inboundMessage = null,
        ?Endpoint $endpoint = null,
        ?EntityDescriptor $partyEntityDescriptor = null,
        string $profileId = Profiles::SSO_IDP_RECEIVE_AUTHN_REQUEST
    ): \LightSaml\Context\Profile\ProfileContext {
        $context = $this->getProfileContext($profileId, $ownRole);

        if ($endpoint instanceof Endpoint) {
            $context->getEndpointContext()->setEndpoint($endpoint);
        }

        if (null == $partyEntityDescriptor) {
            $partyEntityDescriptor = EntityDescriptor::load(__DIR__ . '/../../../../resources/idp2-ed-formatted.xml');
        }
        $context->getPartyEntityContext()->setEntityDescriptor($partyEntityDescriptor);

        if ($inboundMessage instanceof SamlMessage) {
            $context->getInboundContext()->setMessage($inboundMessage);
        }

        return $context;
    }

    protected function criteriaSetShouldHaveBindingCriteria(CriteriaSet $criteriaSet, array $bindings)
    {
        if ($bindings === []) {
            $this->assertFalse($criteriaSet->has(BindingCriteria::class));
        } else {
            $this->assertTrue($criteriaSet->has(BindingCriteria::class));
            /** @var BindingCriteria $criteria */
            $criteria = $criteriaSet->getSingle(BindingCriteria::class);
            $this->assertEquals($bindings, $criteria->getAllBindings());
        }
    }

    protected function criteriaSetShouldHaveDescriptorTypeCriteria(CriteriaSet $criteriaSet, string $value)
    {
        if ($value !== '') {
            $this->assertTrue($criteriaSet->has(DescriptorTypeCriteria::class));
            /** @var DescriptorTypeCriteria $criteria */
            $criteria = $criteriaSet->getSingle(DescriptorTypeCriteria::class);
            $this->assertEquals($value, $criteria->getDescriptorType());
        } else {
            $this->assertFalse($criteriaSet->has(DescriptorTypeCriteria::class));
        }
    }

    protected function criteriaSetShouldHaveServiceTypeCriteria(CriteriaSet $criteriaSet, string $value): void
    {
        $this->assertTrue($criteriaSet->has(ServiceTypeCriteria::class));
        /** @var ServiceTypeCriteria $criteria */
        $criteria = $criteriaSet->getSingle(ServiceTypeCriteria::class);
        $this->assertEquals($value, $criteria->getServiceType());
    }

    protected function criteriaSetShouldHaveIndexCriteria(CriteriaSet $criteriaSet, string $value)
    {
        if ($value !== '') {
            $this->assertTrue($criteriaSet->has(IndexCriteria::class));
            /** @var IndexCriteria $criteria */
            $criteria = $criteriaSet->getSingle(IndexCriteria::class);
            $this->assertEquals($value, $criteria->getIndex());
        } else {
            $this->assertFalse($criteriaSet->has(IndexCriteria::class));
        }
    }

    protected function criteriaSetShouldHaveLocationCriteria(CriteriaSet $criteriaSet, string $value)
    {
        if ($value !== '') {
            $this->assertTrue($criteriaSet->has(LocationCriteria::class));
            /** @var LocationCriteria $criteria */
            $criteria = $criteriaSet->getSingle(LocationCriteria::class);
            $this->assertEquals($value, $criteria->getLocation());
        } else {
            $this->assertFalse($criteriaSet->has(LocationCriteria::class));
        }
    }
}
