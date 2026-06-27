<?php

namespace LightSaml\Model\Metadata;

use DOMNode;
use LightSaml\Model\Assertion\Attribute;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LightSaml\SamlConstants;

class IdpSsoDescriptor extends SSODescriptor
{
    protected ?bool $wantAuthnRequestsSigned = null;

    /** @var SingleSignOnService[]|null */
    protected ?array $singleSignOnServices = null;

    /** @var Attribute[]|null */
    protected ?array $attributes = null;

    public function setWantAuthnRequestsSigned(mixed $wantAuthnRequestsSigned): static
    {
        $this->wantAuthnRequestsSigned = filter_var($wantAuthnRequestsSigned, FILTER_VALIDATE_BOOLEAN, ['flags' => FILTER_NULL_ON_FAILURE]);

        return $this;
    }

    public function getWantAuthnRequestsSigned(): ?bool
    {
        return $this->wantAuthnRequestsSigned;
    }

    public function addSingleSignOnService(SingleSignOnService $singleSignOnService): static
    {
        if (false == is_array($this->singleSignOnServices)) {
            $this->singleSignOnServices = [];
        }
        $this->singleSignOnServices[] = $singleSignOnService;

        return $this;
    }

    /**
     * @return SingleSignOnService[]|null
     */
    public function getAllSingleSignOnServices(): ?array
    {
        return $this->singleSignOnServices;
    }

    /**
     * @return SingleSignOnService[]
     */
    public function getAllSingleSignOnServicesByUrl(string $url): array
    {
        $result = [];
        foreach ($this->getAllSingleSignOnServices() as $svc) {
            if ($svc->getLocation() == $url) {
                $result[] = $svc;
            }
        }

        return $result;
    }

    /**
     * @return SingleSignOnService[]
     */
    public function getAllSingleSignOnServicesByBinding(string $binding): array
    {
        $result = [];
        foreach ($this->getAllSingleSignOnServices() as $svc) {
            if ($svc->getBinding() == $binding) {
                $result[] = $svc;
            }
        }

        return $result;
    }

    public function getFirstSingleSignOnService(?string $binding = null): ?SingleSignOnService
    {
        foreach ($this->getAllSingleSignOnServices() as $svc) {
            if (null == $binding || $svc->getBinding() == $binding) {
                return $svc;
            }
        }

        return null;
    }

    public function addAttribute(Attribute $attribute): static
    {
        if (false == is_array($this->attributes)) {
            $this->attributes = [];
        }
        $this->attributes[] = $attribute;

        return $this;
    }

    /**
     * @return Attribute[]|null
     */
    public function getAllAttributes(): ?array
    {
        return $this->attributes;
    }

    public function serialize(DOMNode $parent, SerializationContext $context): void
    {
        $result = $this->createElement('IDPSSODescriptor', SamlConstants::NS_METADATA, $parent, $context);

        parent::serialize($result, $context);

        $this->attributesToXml(['WantAuthnRequestsSigned'], $result);

        if ($this->getAllSingleSignOnServices()) {
            foreach ($this->getAllSingleSignOnServices() as $object) {
                $object->serialize($result, $context);
            }
        }
        if ($this->getAllAttributes()) {
            foreach ($this->getAllAttributes() as $object) {
                $object->serialize($result, $context);
            }
        }
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'IDPSSODescriptor', SamlConstants::NS_METADATA);

        parent::deserialize($node, $context);

        $this->attributesFromXml($node, ['WantAuthnRequestsSigned']);

        $this->singleSignOnServices = [];
        $this->manyElementsFromXml(
            $node,
            $context,
            'SingleSignOnService',
            'md',
            SingleSignOnService::class,
            'addSingleSignOnService'
        );

        $this->attributes = [];
        $this->manyElementsFromXml(
            $node,
            $context,
            'Attribute',
            'saml',
            Attribute::class,
            'addAttribute'
        );
    }
}
