<?php

namespace LightSaml\Credential\Criteria;

class EntityIdCriteria implements TrustCriteriaInterface
{
    /**
     * @param string $entityId
     */
    public function __construct(protected $entityId)
    {
    }

    /**
     * @return string
     */
    public function getEntityId()
    {
        return $this->entityId;
    }
}
