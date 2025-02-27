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
use Symfony\Component\HttpFoundation\Session\SessionInterface;

abstract class BaseTestCase extends TestCase
{
    /**
     * @return MockObject|LoggerInterface
     */
    public function getLoggerMock()
    {
        return $this->getMockBuilder(LoggerInterface::class)->getMock();
    }

    /**
     * @return MockObject|TimeProviderInterface
     */
    public function getTimeProviderMock()
    {
        return $this->getMockBuilder(TimeProviderInterface::class)->getMock();
    }

    /**
     * @return MockObject|EndpointReference
     */
    public function getEndpointReferenceMock(Endpoint $endpoint)
    {
        $endpointReferenceMock = $this->getMockBuilder(EndpointReference::class)->disableOriginalConstructor()->getMock();

        $endpointReferenceMock->expects($this->any())
            ->method('getEndpoint')
            ->willReturn($endpoint);

        return $endpointReferenceMock;
    }

    /**
     * @return MockObject|EndpointResolverInterface
     */
    public function getEndpointResolverMock()
    {
        return $this->getMockBuilder(EndpointResolverInterface::class)->getMock();
    }

    /**
     * @param string $profileId
     * @param string $ownRole
     *
     * @return ProfileContext
     */
    public function getProfileContext($profileId = Profiles::SSO_IDP_RECEIVE_AUTHN_REQUEST, $ownRole = ProfileContext::ROLE_IDP)
    {
        return new ProfileContext($profileId, $ownRole);
    }

    /**
     * @return AssertionContext
     */
    public function getAssertionContext(Assertion $assertion)
    {
        $context = new AssertionContext();

        if ($assertion) {
            $context->setAssertion($assertion);
        }

        return $context;
    }

    /**
     * @return MockObject|RequestStateStoreInterface
     */
    public function getRequestStateStoreMock()
    {
        return $this->getMockBuilder(RequestStateStoreInterface::class)->getMock();
    }

    /**
     * @return MockObject|BindingFactoryInterface
     */
    public function getBindingFactoryMock()
    {
        return $this->getMockBuilder(BindingFactoryInterface::class)->getMock();
    }

    /**
     * @return MockObject|AbstractBinding
     */
    public function getBindingMock()
    {
        return $this->getMockForAbstractClass(AbstractBinding::class);
    }

    /**
     * @return MockObject|SignatureResolverInterface
     */
    public function getSignatureResolverMock()
    {
        return $this->getMockBuilder(SignatureResolverInterface::class)->getMock();
    }

    /**
     * @return MockObject|X509Certificate
     */
    public function getX509CertificateMock()
    {
        return $this->getMockBuilder(X509Certificate::class)->getMock();
    }

    /**
     * @return MockObject|AssertionValidatorInterface
     */
    public function getAssertionValidatorMock()
    {
        return $this->getMockBuilder(AssertionValidatorInterface::class)->getMock();
    }

    /**
     * @return MockObject|EntityDescriptorStoreInterface
     */
    public function getEntityDescriptorStoreMock()
    {
        return $this->getMockBuilder(EntityDescriptorStoreInterface::class)->getMock();
    }

    /**
     * @param string $class
     * @param string $getter
     * @param string $value
     */
    public function assertCriteria(CriteriaSet $criteriaSet, $class, $getter, $value)
    {
        $this->assertTrue($criteriaSet->has($class));
        $criteria = $criteriaSet->getSingle($class);
        if ($getter) {
            $this->assertEquals($value, $criteria->{$getter}());
        }
    }

    /**
     * @return MockObject|IdStoreInterface
     */
    public function getIdStoreMock()
    {
        return $this->getMockBuilder(IdStoreInterface::class)->getMock();
    }

    /**
     * @return MockObject|AssertionTimeValidatorInterface
     */
    public function getAssertionTimeValidatorMock()
    {
        return $this->getMockBuilder(AssertionTimeValidatorInterface::class)->getMock();
    }

    /**
     * @return MockObject|CriteriaInterface
     */
    public function getCriteriaMock()
    {
        return $this->getMockBuilder(CriteriaInterface::class)->getMock();
    }

    /**
     * @return MockObject|CredentialResolverInterface
     */
    public function getCredentialResolverMock()
    {
        return $this->getMockBuilder(CredentialResolverInterface::class)->getMock();
    }

    /**
     * @return MockObject|X509CredentialInterface
     */
    public function getX509CredentialMock()
    {
        return $this->getMockBuilder(X509CredentialInterface::class)->getMock();
    }

    /**
     * @return MockObject|SessionInterface
     */
    public function getSessionMock()
    {
        return $this->getMockBuilder(SessionInterface::class)->getMock();
    }

    /**
     * @return MockObject|XMLSecurityKey
     */
    public function getXmlSecurityKeyMock()
    {
        return $this->getMockBuilder(XMLSecurityKey::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }
}
