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
    public function getLoggerMock(): \PHPUnit\Framework\MockObject\MockObject|\Psr\Log\LoggerInterface
    {
        return $this->getMockBuilder(LoggerInterface::class)->getMock();
    }

    public function getTimeProviderMock(): \PHPUnit\Framework\MockObject\MockObject|\LightSaml\Provider\TimeProvider\TimeProviderInterface
    {
        return $this->getMockBuilder(TimeProviderInterface::class)->getMock();
    }

    public function getEndpointReferenceMock(Endpoint $endpoint): \PHPUnit\Framework\MockObject\MockObject|\LightSaml\Model\Metadata\EndpointReference
    {
        $endpointReferenceMock = $this->getMockBuilder(EndpointReference::class)->disableOriginalConstructor()->getMock();

        $endpointReferenceMock->expects($this->any())
            ->method('getEndpoint')
            ->willReturn($endpoint);

        return $endpointReferenceMock;
    }

    public function getEndpointResolverMock(): \PHPUnit\Framework\MockObject\MockObject|\LightSaml\Resolver\Endpoint\EndpointResolverInterface
    {
        return $this->getMockBuilder(EndpointResolverInterface::class)->getMock();
    }

    
    public function getProfileContext(string $profileId = Profiles::SSO_IDP_RECEIVE_AUTHN_REQUEST, string $ownRole = ProfileContext::ROLE_IDP): \LightSaml\Context\Profile\ProfileContext
    {
        return new ProfileContext($profileId, $ownRole);
    }

    public function getAssertionContext(Assertion $assertion): \LightSaml\Context\Profile\AssertionContext
    {
        $context = new AssertionContext();

        if ($assertion) {
            $context->setAssertion($assertion);
        }

        return $context;
    }

    public function getRequestStateStoreMock(): \PHPUnit\Framework\MockObject\MockObject|\LightSaml\Store\Request\RequestStateStoreInterface
    {
        return $this->getMockBuilder(RequestStateStoreInterface::class)->getMock();
    }

    public function getBindingFactoryMock(): \PHPUnit\Framework\MockObject\MockObject|\LightSaml\Binding\BindingFactoryInterface
    {
        return $this->getMockBuilder(BindingFactoryInterface::class)->getMock();
    }

    public function getBindingMock(): \PHPUnit\Framework\MockObject\MockObject|\LightSaml\Binding\AbstractBinding
    {
        return $this->getMockForAbstractClass(AbstractBinding::class);
    }

    public function getSignatureResolverMock(): \PHPUnit\Framework\MockObject\MockObject|\LightSaml\Resolver\Signature\SignatureResolverInterface
    {
        return $this->getMockBuilder(SignatureResolverInterface::class)->getMock();
    }

    public function getX509CertificateMock(): \PHPUnit\Framework\MockObject\MockObject|\LightSaml\Credential\X509Certificate
    {
        return $this->getMockBuilder(X509Certificate::class)->getMock();
    }

    public function getAssertionValidatorMock(): \PHPUnit\Framework\MockObject\MockObject|\LightSaml\Validator\Model\Assertion\AssertionValidatorInterface
    {
        return $this->getMockBuilder(AssertionValidatorInterface::class)->getMock();
    }

    public function getEntityDescriptorStoreMock(): \PHPUnit\Framework\MockObject\MockObject|\LightSaml\Store\EntityDescriptor\EntityDescriptorStoreInterface
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

    public function getIdStoreMock(): \PHPUnit\Framework\MockObject\MockObject|\LightSaml\Store\Id\IdStoreInterface
    {
        return $this->getMockBuilder(IdStoreInterface::class)->getMock();
    }

    public function getAssertionTimeValidatorMock(): \PHPUnit\Framework\MockObject\MockObject|\LightSaml\Validator\Model\Assertion\AssertionTimeValidatorInterface
    {
        return $this->getMockBuilder(AssertionTimeValidatorInterface::class)->getMock();
    }

    public function getCriteriaMock(): \PHPUnit\Framework\MockObject\MockObject|\LightSaml\Criteria\CriteriaInterface
    {
        return $this->getMockBuilder(CriteriaInterface::class)->getMock();
    }

    public function getCredentialResolverMock(): \PHPUnit\Framework\MockObject\MockObject|\LightSaml\Resolver\Credential\CredentialResolverInterface
    {
        return $this->getMockBuilder(CredentialResolverInterface::class)->getMock();
    }

    public function getX509CredentialMock(): \PHPUnit\Framework\MockObject\MockObject|\LightSaml\Credential\X509CredentialInterface
    {
        return $this->getMockBuilder(X509CredentialInterface::class)->getMock();
    }

    public function getXmlSecurityKeyMock(): \PHPUnit\Framework\MockObject\MockObject|\RobRichards\XMLSecLibs\XMLSecurityKey
    {
        return $this->getMockBuilder(XMLSecurityKey::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }
}
