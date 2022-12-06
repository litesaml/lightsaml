<?php

namespace LightSaml\Resolver\Credential;

use LightSaml\Credential\CredentialInterface;
use LightSaml\Credential\Criteria\PrivateKeyCriteria;
use LightSaml\Criteria\CriteriaSet;

class PrivateKeyResolver extends AbstractQueryableResolver
{
    /**
     * @param CredentialInterface[] $arrCredentials
     *
     * @return CredentialInterface[]
     */
    public function resolve(CriteriaSet $criteriaSet, array $arrCredentials = [])
    {
        if (false == $criteriaSet->has(PrivateKeyCriteria::class)) {
            return $arrCredentials;
        }

        $result = [];
        foreach ($arrCredentials as $credential) {
            if ($credential->getPrivateKey()) {
                $result[] = $credential;
            }
        }

        return $result;
    }
}
