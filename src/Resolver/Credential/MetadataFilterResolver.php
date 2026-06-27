<?php

namespace LightSaml\Resolver\Credential;

use LightSaml\Credential\Context\MetadataCredentialContext;
use LightSaml\Credential\CredentialInterface;
use LightSaml\Credential\Criteria\MetadataCriteria;
use LightSaml\Criteria\CriteriaSet;
use LightSaml\Model\Metadata\IdpSsoDescriptor;
use LightSaml\Model\Metadata\SpSsoDescriptor;

class MetadataFilterResolver extends AbstractQueryableResolver
{
    /**
     * @param CredentialInterface[] $arrCredentials
     *
     * @return CredentialInterface[]
     */
    public function resolve(CriteriaSet $criteriaSet, array $arrCredentials = []): array
    {
        if (false == $criteriaSet->has(MetadataCriteria::class)) {
            return $arrCredentials;
        }

        $result = [];
        foreach ($criteriaSet->get(MetadataCriteria::class) as $criteria) {
            foreach ($arrCredentials as $credential) {
                $metadataContext = $credential->getCredentialContext()->get(MetadataCredentialContext::class);
                if (
                    null === $metadataContext
                    || ($metadataContext instanceof MetadataCredentialContext && MetadataCriteria::TYPE_IDP == $criteria->getMetadataType() && $metadataContext->getRoleDescriptor() instanceof IdpSsoDescriptor)
                    || ($metadataContext instanceof MetadataCredentialContext && MetadataCriteria::TYPE_SP == $criteria->getMetadataType() && $metadataContext->getRoleDescriptor() instanceof SpSsoDescriptor)
                ) {
                    $result[] = $credential;
                }
            }
        }

        return $result;
    }
}
