<?php

namespace LightSaml\Validator\Model\Signature;

use LightSaml\Credential\CredentialInterface;
use LightSaml\Credential\Criteria\EntityIdCriteria;
use LightSaml\Credential\Criteria\MetadataCriteria;
use LightSaml\Credential\Criteria\PublicKeyThumbprintCriteria;
use LightSaml\Credential\Criteria\UsageCriteria;
use LightSaml\Credential\UsageType;
use LightSaml\Error\LightSamlSecurityException;
use LightSaml\Model\XmlDSig\AbstractSignatureReader;
use LightSaml\Resolver\Credential\CredentialResolverInterface;
use LightSaml\SamlConstants;

class SignatureValidator implements SignatureValidatorInterface
{
    public function __construct(protected CredentialResolverInterface $credentialResolver)
    {
    }

    /**
     * @param string $issuer
     * @param string $metadataType
     *
     * @return CredentialInterface|null
     */
    public function validate(AbstractSignatureReader $signature, $issuer, $metadataType)
    {
        $query = $this->credentialResolver->query();
        $query
            ->add(new EntityIdCriteria($issuer))
            ->add(new MetadataCriteria($metadataType, SamlConstants::VERSION_20))
            ->add(new UsageCriteria(UsageType::SIGNING))
        ;
        if ($signature->getKey() && $signature->getKey()->getX509Thumbprint()) {
            $query->add(new PublicKeyThumbprintCriteria($signature->getKey()->getX509Thumbprint()));
        }
        $query->resolve();

        $credentialCandidates = $query->allCredentials();
        if (empty($credentialCandidates)) {
            throw new LightSamlSecurityException('No credentials resolved for signature verification');
        }

        return $signature->validateMulti($credentialCandidates);
    }
}
