<?php

namespace LightSaml\Resolver\Credential;

use LightSaml\Credential\CredentialInterface;
use LightSaml\Credential\Criteria\AlgorithmCriteria;
use LightSaml\Criteria\CriteriaSet;

class AlgorithmFilterResolver extends AbstractQueryableResolver
{
    /**
     * @param CredentialInterface[] $arrCredentials
     *
     * @return CredentialInterface[]
     */
    public function resolve(CriteriaSet $criteriaSet, array $arrCredentials = [])
    {
        if (false == $criteriaSet->has(AlgorithmCriteria::class)) {
            return $arrCredentials;
        }

        $result = [];
        foreach ($criteriaSet->get(AlgorithmCriteria::class) as $criteria) {
            /* @var AlgorithmCriteria $criteria */
            foreach ($arrCredentials as $credential) {
                if (
                    ($credential->getPrivateKey() && $credential->getPrivateKey()->getAlgorith() == $criteria->getAlgorithm())
                    || ($credential->getPublicKey() && $credential->getPublicKey()->getAlgorith() == $criteria->getAlgorithm())
                ) {
                    $result[] = $credential;
                }
            }
        }

        return $result;
    }
}
