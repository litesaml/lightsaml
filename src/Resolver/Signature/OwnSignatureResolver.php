<?php

namespace LightSaml\Resolver\Signature;

use LightSaml\Context\Profile\AbstractProfileContext;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Credential\Criteria\EntityIdCriteria;
use LightSaml\Credential\Criteria\MetadataCriteria;
use LightSaml\Credential\Criteria\UsageCriteria;
use LightSaml\Credential\Criteria\X509CredentialCriteria;
use LightSaml\Credential\UsageType;
use LightSaml\Credential\X509CredentialInterface;
use LightSaml\Error\LightSamlContextException;
use LightSaml\Model\XmlDSig\SignatureWriter;
use LightSaml\Resolver\Credential\CredentialResolverInterface;
use LightSaml\SamlConstants;
use LogicException;

class OwnSignatureResolver implements SignatureResolverInterface
{
    public function __construct(protected CredentialResolverInterface $credentialResolver)
    {
    }

    /**
     * @return SignatureWriter
     */
    public function getSignature(AbstractProfileContext $context)
    {
        $credential = $this->getSigningCredential($context);
        if (null == $credential) {
            throw new LightSamlContextException($context, 'Unable to find signing credential');
        }
        $trustOptions = $context->getProfileContext()->getTrustOptions();

        return new SignatureWriter($credential->getCertificate(), $credential->getPrivateKey(), $trustOptions->getSignatureDigestAlgorithm());
    }

    /**
     * @return X509CredentialInterface|null
     */
    private function getSigningCredential(AbstractProfileContext $context)
    {
        $profileContext = $context->getProfileContext();

        $entityDescriptor = $profileContext->getOwnEntityDescriptor();

        $query = $this->credentialResolver->query();
        $query
            ->add(new EntityIdCriteria($entityDescriptor->getEntityID()))
            ->add(new UsageCriteria(UsageType::SIGNING))
            ->add(new X509CredentialCriteria())
            ->addIf(ProfileContext::ROLE_IDP === $profileContext->getOwnRole(), function () {
                return new MetadataCriteria(MetadataCriteria::TYPE_IDP, SamlConstants::VERSION_20);
            })
            ->addIf(ProfileContext::ROLE_SP === $profileContext->getOwnRole(), function () {
                return new MetadataCriteria(MetadataCriteria::TYPE_SP, SamlConstants::VERSION_20);
            })
        ;
        $query->resolve();

        $result = $query->firstCredential();
        if ($result && false === $result instanceof X509CredentialInterface) {
            throw new LogicException(sprintf('Expected X509CredentialInterface but got %s', $result::class));
        }

        return $result;
    }
}
