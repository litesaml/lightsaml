<?php

namespace LightSaml\Model\Metadata;

use DOMNode;
use LightSaml\Context\Model\DeserializationContext;
use LightSaml\Context\Model\SerializationContext;
use LightSaml\SamlConstants;

class SpSsoDescriptor extends SSODescriptor
{
    protected ?bool $authnRequestsSigned = null;

    protected ?bool $wantAssertionsSigned = null;

    /** @var AssertionConsumerService[]|null */
    protected ?array $assertionConsumerServices = null;

    public function addAssertionConsumerService(AssertionConsumerService $assertionConsumerService): static
    {
        if (false == is_array($this->assertionConsumerServices)) {
            $this->assertionConsumerServices = [];
        }
        if (null === $assertionConsumerService->getIndex()) {
            $assertionConsumerService->setIndex(count($this->assertionConsumerServices));
        }
        $this->assertionConsumerServices[] = $assertionConsumerService;

        return $this;
    }

    /**
     * @return AssertionConsumerService[]|null
     */
    public function getAllAssertionConsumerServices(): ?array
    {
        return $this->assertionConsumerServices;
    }

    /**
     * @return AssertionConsumerService[]
     */
    public function getAllAssertionConsumerServicesByBinding(string $binding): array
    {
        $result = [];
        foreach ($this->getAllAssertionConsumerServices() as $svc) {
            if ($svc->getBinding() == $binding) {
                $result[] = $svc;
            }
        }

        return $result;
    }

    /**
     * @return AssertionConsumerService[]
     */
    public function getAllAssertionConsumerServicesByUrl(string $url): array
    {
        $result = [];
        foreach ($this->getAllAssertionConsumerServices() as $svc) {
            if ($svc->getLocation() == $url) {
                $result[] = $svc;
            }
        }

        return $result;
    }

    public function getAssertionConsumerServicesByIndex(int $index): ?AssertionConsumerService
    {
        foreach ($this->getAllAssertionConsumerServices() as $svc) {
            if ($svc->getIndex() == $index) {
                return $svc;
            }
        }

        return null;
    }

    public function getFirstAssertionConsumerService(?string $binding = null): ?AssertionConsumerService
    {
        foreach ($this->getAllAssertionConsumerServices() as $svc) {
            if (null == $binding || $svc->getBinding() == $binding) {
                return $svc;
            }
        }

        return null;
    }

    public function setAuthnRequestsSigned(mixed $authnRequestsSigned): static
    {
        $this->authnRequestsSigned = filter_var($authnRequestsSigned, FILTER_VALIDATE_BOOLEAN, ['flags' => FILTER_NULL_ON_FAILURE]);

        return $this;
    }

    public function getAuthnRequestsSigned(): ?bool
    {
        return $this->authnRequestsSigned;
    }

    public function setWantAssertionsSigned(mixed $wantAssertionsSigned): static
    {
        $this->wantAssertionsSigned = filter_var($wantAssertionsSigned, FILTER_VALIDATE_BOOLEAN, ['flags' => FILTER_NULL_ON_FAILURE]);

        return $this;
    }

    public function getWantAssertionsSigned(): ?bool
    {
        return $this->wantAssertionsSigned;
    }

    public function serialize(DOMNode $parent, SerializationContext $context): void
    {
        $result = $this->createElement('SPSSODescriptor', SamlConstants::NS_METADATA, $parent, $context);

        parent::serialize($result, $context);

        $this->attributesToXml(['AuthnRequestsSigned', 'WantAssertionsSigned'], $result);

        $this->manyElementsToXml($this->getAllAssertionConsumerServices(), $result, $context, null);
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'SPSSODescriptor', SamlConstants::NS_METADATA);

        parent::deserialize($node, $context);

        $this->attributesFromXml($node, ['AuthnRequestsSigned', 'WantAssertionsSigned']);

        $this->manyElementsFromXml(
            $node,
            $context,
            'AssertionConsumerService',
            'md',
            AssertionConsumerService::class,
            'addAssertionConsumerService'
        );
    }
}
