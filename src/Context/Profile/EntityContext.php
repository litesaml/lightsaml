<?php

namespace LightSaml\Context\Profile;

use LightSaml\Meta\TrustOptions\TrustOptions;
use LightSaml\Model\Metadata\EntityDescriptor;

class EntityContext extends AbstractProfileContext
{
    private ?string $entityId = null;

    private ?\LightSaml\Model\Metadata\EntityDescriptor $entityDescriptor = null;

    private ?\LightSaml\Meta\TrustOptions\TrustOptions $trustOptions = null;

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
     * @return EntityDescriptor
     */
    public function getEntityDescriptor(): ?\LightSaml\Model\Metadata\EntityDescriptor
    {
        return $this->entityDescriptor;
    }

    public function setEntityDescriptor(EntityDescriptor $entityDescriptor): static
    {
        $this->entityDescriptor = $entityDescriptor;

        return $this;
    }

    /**
     * @return TrustOptions
     */
    public function getTrustOptions(): ?\LightSaml\Meta\TrustOptions\TrustOptions
    {
        return $this->trustOptions;
    }

    public function setTrustOptions(TrustOptions $trustOptions): static
    {
        $this->trustOptions = $trustOptions;

        return $this;
    }
}
