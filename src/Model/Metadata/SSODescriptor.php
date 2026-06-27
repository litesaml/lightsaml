<?php

namespace LightSaml\Model\Metadata;

use DOMNode;
use LightSaml\Context\Model\DeserializationContext;
use LightSaml\Context\Model\SerializationContext;
use LightSaml\SamlConstants;

abstract class SSODescriptor extends RoleDescriptor
{
    /** @var SingleLogoutService[] */
    protected array $singleLogoutServices = [];

    /** @var string[]|null */
    protected ?array $nameIDFormats = null;

    public function addSingleLogoutService(SingleLogoutService $singleLogoutService): static
    {
        $this->singleLogoutServices[] = $singleLogoutService;

        return $this;
    }

    /**
     * @return SingleLogoutService[]
     */
    public function getAllSingleLogoutServices(): array
    {
        return $this->singleLogoutServices;
    }

    /**
     * @return SingleLogoutService[]
     */
    public function getAllSingleLogoutServicesByBinding(string $binding): array
    {
        $result = [];
        foreach ($this->getAllSingleLogoutServices() as $svc) {
            if ($binding == $svc->getBinding()) {
                $result[] = $svc;
            }
        }

        return $result;
    }

    public function getFirstSingleLogoutService(?string $binding = null): ?SingleLogoutService
    {
        foreach ($this->getAllSingleLogoutServices() as $svc) {
            if (null == $binding || $binding == $svc->getBinding()) {
                return $svc;
            }
        }

        return null;
    }

    public function addNameIDFormat(string $nameIDFormat): static
    {
        $this->nameIDFormats[] = $nameIDFormat;

        return $this;
    }

    /**
     * @return string[]|null
     */
    public function getAllNameIDFormats(): ?array
    {
        return $this->nameIDFormats;
    }

    public function hasNameIDFormat(string $nameIdFormat): bool
    {
        if ($this->nameIDFormats) {
            foreach ($this->nameIDFormats as $format) {
                if ($format == $nameIdFormat) {
                    return true;
                }
            }
        }

        return false;
    }

    public function serialize(DOMNode $parent, SerializationContext $context): void
    {
        parent::serialize($parent, $context);

        $this->manyElementsToXml($this->getAllSingleLogoutServices(), $parent, $context, null);
        $this->manyElementsToXml($this->getAllNameIDFormats(), $parent, $context, 'NameIDFormat', SamlConstants::NS_METADATA);
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): void
    {
        parent::deserialize($node, $context);

        $this->manyElementsFromXml($node, $context, 'NameIDFormat', 'md', null, 'addNameIDFormat');

        $this->manyElementsFromXml(
            $node,
            $context,
            'SingleLogoutService',
            'md',
            SingleLogoutService::class,
            'addSingleLogoutService'
        );
    }
}
