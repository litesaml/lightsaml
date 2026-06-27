<?php

namespace LightSaml\Context\Profile;

use LightSaml\Meta\TrustOptions\TrustOptions;
use LightSaml\Model\Metadata\EntityDescriptor;

class EntityContext extends AbstractProfileContext
{
    private ?string $entityId = null;

    private ?EntityDescriptor $entityDescriptor = null;

    private ?TrustOptions $trustOptions = null;

    public function getEntityId(): ?string
    {
        return $this->entityId;
    }

    public function setEntityId(string $entityId): static
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     */
    public function getEntityDescriptor(): ?EntityDescriptor
    {
        return $this->entityDescriptor;
    }

    public function setEntityDescriptor(EntityDescriptor $entityDescriptor): static
    {
        $this->entityDescriptor = $entityDescriptor;

        return $this;
    }

    /**
     */
    public function getTrustOptions(): ?TrustOptions
    {
        return $this->trustOptions;
    }

    public function setTrustOptions(TrustOptions $trustOptions): static
    {
        $this->trustOptions = $trustOptions;

        return $this;
    }
}
