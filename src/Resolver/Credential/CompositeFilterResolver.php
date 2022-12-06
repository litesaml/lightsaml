<?php

namespace LightSaml\Resolver\Credential;

use LightSaml\Credential\CredentialInterface;
use LightSaml\Criteria\CriteriaSet;

class CompositeFilterResolver extends AbstractCompositeResolver
{
    /**
     * @param CredentialInterface[] $arrCredentials
     *
     * @return CredentialInterface[]
     */
    public function resolve(CriteriaSet $criteriaSet, array $arrCredentials = [])
    {
        $result = $arrCredentials;
        foreach ($this->resolvers as $resolver) {
            $result = $resolver->resolve($criteriaSet, $result);
        }

        return $result;
    }
}
