<?php

namespace LightSaml\Resolver\Credential;

use LightSaml\Credential\CredentialInterface;
use LightSaml\Credential\Criteria\PublicKeyThumbprintCriteria;
use LightSaml\Criteria\CriteriaSet;

class PublicKeyThumbprintResolver extends AbstractQueryableResolver
{
    /**
     * @param CredentialInterface[] $arrCredentials
     *
     * @return CredentialInterface[]
     */
    public function resolve(CriteriaSet $criteriaSet, array $arrCredentials = [])
    {
        if (false == $criteriaSet->has(PublicKeyThumbprintCriteria::class)) {
            return $arrCredentials;
        }

        $result = [];
        /** @var PublicKeyThumbprintCriteria $criteria */
        foreach ($criteriaSet->get(PublicKeyThumbprintCriteria::class) as $criteria) {
            foreach ($arrCredentials as $credential) {
                if ($credential->getPublicKey() && $credential->getPublicKey()->getX509Thumbprint() == $criteria->getThumbprint()) {
                    $result[] = $credential;
                }
            }
        }

        return $result;
    }
}
