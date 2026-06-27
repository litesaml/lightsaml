<?php

namespace Tests;

use LightSaml\Binding\AbstractBinding;
use LightSaml\Binding\BindingFactoryInterface;
use LightSaml\Context\Profile\AssertionContext;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Credential\X509Certificate;
use LightSaml\Credential\X509CredentialInterface;
use LightSaml\Criteria\CriteriaInterface;
use LightSaml\Criteria\CriteriaSet;
use LightSaml\Model\Assertion\Assertion;
use LightSaml\Model\Metadata\Endpoint;
use LightSaml\Model\Metadata\EndpointReference;
use LightSaml\Profile\Profiles;
use LightSaml\Provider\TimeProvider\TimeProviderInterface;
use LightSaml\Resolver\Credential\CredentialResolverInterface;
use LightSaml\Resolver\Endpoint\EndpointResolverInterface;
use LightSaml\Resolver\Signature\SignatureResolverInterface;
use LightSaml\Store\EntityDescriptor\EntityDescriptorStoreInterface;
use LightSaml\Store\Id\IdStoreInterface;
use LightSaml\Store\Request\RequestStateStoreInterface;
use LightSaml\Validator\Model\Assertion\AssertionTimeValidatorInterface;
use LightSaml\Validator\Model\Assertion\AssertionValidatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RobRichards\XMLSecLibs\XMLSecurityKey;

abstract class BaseTestCase extends TestCase
{
    public function getLoggerMock(): MockObject|LoggerInterface
    {
        return $this->getMockBuilder(LoggerInterface::class)->getMock();
    }

    public function getTimeProviderMock(): MockObject|TimeProviderInterface
    {
        return $this->getMockBuilder(TimeProviderInterface::class)->getMock();
    }

    public function getEndpointReferenceMock(Endpoint $endpoint): MockObject|EndpointReference
    {
        $endpointReferenceMock = $this->getMockBuilder(EndpointReference::class)->disableOriginalConstructor()->getMock();

        $endpointReferenceMock->expects($this->any())
            ->method('getEndpoint')
            ->willReturn($endpoint);

        return $endpointReferenceMock;
    }

    public function getEndpointResolverMock(): MockObject|EndpointResolverInterface
    {
        return $this->getMockBuilder(EndpointResolverInterface::class)->getMock();
    }

    public function getProfileContext(string $profileId = Profiles::SSO_IDP_RECEIVE_AUTHN_REQUEST, string $ownRole = ProfileContext::ROLE_IDP): ProfileContext
    {
        return new ProfileContext($profileId, $ownRole);
    }

    public function getAssertionContext(Assertion $assertion): AssertionContext
    {
        $context = new AssertionContext();

        if ($assertion) {
            $context->setAssertion($assertion);
        }

        return $context;
    }

    public function getRequestStateStoreMock(): MockObject|RequestStateStoreInterface
    {
        return $this->getMockBuilder(RequestStateStoreInterface::class)->getMock();
    }

    public function getBindingFactoryMock(): MockObject|BindingFactoryInterface
    {
        return $this->getMockBuilder(BindingFactoryInterface::class)->getMock();
    }

    public function getBindingMock(): MockObject|AbstractBinding
    {
        return $this->getMockForAbstractClass(AbstractBinding::class);
    }

    public function getSignatureResolverMock(): MockObject|SignatureResolverInterface
    {
        return $this->getMockBuilder(SignatureResolverInterface::class)->getMock();
    }

    public function getX509CertificateMock(): MockObject|X509Certificate
    {
        return $this->getMockBuilder(X509Certificate::class)->getMock();
    }

    public function getAssertionValidatorMock(): MockObject|AssertionValidatorInterface
    {
        return $this->getMockBuilder(AssertionValidatorInterface::class)->getMock();
    }

    public function getEntityDescriptorStoreMock(): MockObject|EntityDescriptorStoreInterface
    {
        return $this->getMockBuilder(EntityDescriptorStoreInterface::class)->getMock();
    }

    public function assertCriteria(CriteriaSet $criteriaSet, string $class, ?string $getter, ?string $value): void
    {
        $this->assertTrue($criteriaSet->has($class));
        $criteria = $criteriaSet->getSingle($class);
        if ($getter !== null && $getter !== '' && $getter !== '0') {
            $this->assertEquals($value, $criteria->{$getter}());
        }
    }

    public function getIdStoreMock(): MockObject|IdStoreInterface
    {
        return $this->getMockBuilder(IdStoreInterface::class)->getMock();
    }

    public function getAssertionTimeValidatorMock(): MockObject|AssertionTimeValidatorInterface
    {
        return $this->getMockBuilder(AssertionTimeValidatorInterface::class)->getMock();
    }

    public function getCriteriaMock(): MockObject|CriteriaInterface
    {
        return $this->getMockBuilder(CriteriaInterface::class)->getMock();
    }

    public function getCredentialResolverMock(): MockObject|CredentialResolverInterface
    {
        return $this->getMockBuilder(CredentialResolverInterface::class)->getMock();
    }

    public function getX509CredentialMock(): MockObject|X509CredentialInterface
    {
        return $this->getMockBuilder(X509CredentialInterface::class)->getMock();
    }

    public function getXmlSecurityKeyMock(): MockObject|XMLSecurityKey
    {
        return $this->getMockBuilder(XMLSecurityKey::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }
}
