<?php

namespace LightSaml\Resolver\Credential;

use LightSaml\Credential\CredentialInterface;
use LightSaml\Criteria\CriteriaSet;

class CompositeUnionResolver extends AbstractCompositeResolver
{
    /**
     * @param CredentialInterface[] $arrCredentials
     *
     * @return CredentialInterface[]
     */
    public function resolve(CriteriaSet $criteriaSet, array $arrCredentials = [])
    {
        $result = [];
        foreach ($this->resolvers as $resolver) {
            $result = array_merge($result, $resolver->resolve($criteriaSet, $arrCredentials));
        }

        return $result;
    }
}
