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

    public function getEntityId(): string
    {
        return $this->entityId;
    }
}
