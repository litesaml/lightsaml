<?php

namespace LightSaml\Resolver\Endpoint;

use LightSaml\Criteria\CriteriaSet;
use LightSaml\Model\Metadata\EndpointReference;
use LightSaml\Model\Metadata\IndexedEndpoint;
use LightSaml\Resolver\Endpoint\Criteria\IndexCriteria;

class IndexEndpointResolver implements EndpointResolverInterface
{
    /**
     * @param EndpointReference[] $candidates
     *
     * @return EndpointReference[]
     */
    public function resolve(CriteriaSet $criteriaSet, array $candidates)
    {
        if (false === $criteriaSet->has(IndexCriteria::class)) {
            return $candidates;
        }

        $result = [];
        /** @var IndexCriteria $indexCriteria */
        foreach ($criteriaSet->get(IndexCriteria::class) as $indexCriteria) {
            foreach ($candidates as $endpointReference) {
                $endpoint = $endpointReference->getEndpoint();
                if ($endpoint instanceof IndexedEndpoint) {
                    if ($endpoint->getIndex() == $indexCriteria->getIndex()) {
                        $result[] = $endpointReference;
                    }
                }
            }
        }

        return $result;
    }
}
