<?php

namespace LightSaml\Model\Protocol;

use DOMNode;
use LightSaml\Model\Assertion\Conditions;
use LightSaml\Model\Assertion\Subject;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LightSaml\SamlConstants;

class AuthnRequest extends AbstractRequest
{
    protected ?bool $forceAuthn = null;

    protected ?bool $isPassive = null;

    protected ?int $assertionConsumerServiceIndex = null;

    protected ?string $assertionConsumerServiceURL = null;

    protected ?int $attributeConsumingServiceIndex = null;

    protected ?string $protocolBinding = null;

    protected ?string $providerName = null;

    protected ?Conditions $conditions = null;

    protected ?NameIDPolicy $nameIDPolicy = null;

    protected ?Subject $subject = null;

    public function setSubject(Subject $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getSubject(): ?\LightSaml\Model\Assertion\Subject
    {
        return $this->subject;
    }

    public function setProviderName(?string $providerName): static
    {
        $this->providerName = (string) $providerName;

        return $this;
    }

    public function getProviderName(): ?string
    {
        return $this->providerName;
    }

    public function setProtocolBinding(?string $protocolBinding): static
    {
        $this->protocolBinding = (string) $protocolBinding;

        return $this;
    }

    public function getProtocolBinding(): ?string
    {
        return $this->protocolBinding;
    }

    public function setNameIDPolicy(NameIDPolicy $nameIDPolicy): static
    {
        $this->nameIDPolicy = $nameIDPolicy;

        return $this;
    }

    public function getNameIDPolicy(): ?\LightSaml\Model\Protocol\NameIDPolicy
    {
        return $this->nameIDPolicy;
    }

    public function setIsPassive(?bool $isPassive): static
    {
        $this->isPassive = 0 == strcasecmp($isPassive, 'true') || true === $isPassive || 1 == $isPassive;

        return $this;
    }

    public function getIsPassive(): ?bool
    {
        return $this->isPassive;
    }

    public function getIsPassiveString(): ?string
    {
        if (null === $this->isPassive) {
            return null;
        }

        return $this->isPassive ? 'true' : 'false';
    }

    public function setForceAuthn(?bool $forceAuthn): static
    {
        $this->forceAuthn = 0 == strcasecmp($forceAuthn, 'true') || true === $forceAuthn || 1 == $forceAuthn;

        return $this;
    }

    public function getForceAuthn(): ?bool
    {
        return $this->forceAuthn;
    }

    public function getForceAuthnString(): ?string
    {
        if (null === $this->forceAuthn) {
            return null;
        }

        return $this->forceAuthn ? 'true' : 'false';
    }

    public function setConditions(?\LightSaml\Model\Assertion\Conditions $conditions): static
    {
        $this->conditions = $conditions;

        return $this;
    }

    public function getConditions(): ?\LightSaml\Model\Assertion\Conditions
    {
        return $this->conditions;
    }

    public function setAttributeConsumingServiceIndex(?int $attributeConsumingServiceIndex): static
    {
        $this->attributeConsumingServiceIndex = null !== $attributeConsumingServiceIndex
            ? intval(((string) $attributeConsumingServiceIndex))
            : null;

        return $this;
    }

    public function getAttributeConsumingServiceIndex(): ?int
    {
        return $this->attributeConsumingServiceIndex;
    }

    public function setAssertionConsumerServiceURL(?string $assertionConsumerServiceURL): static
    {
        $this->assertionConsumerServiceURL = (string) $assertionConsumerServiceURL;

        return $this;
    }

    public function getAssertionConsumerServiceURL(): ?string
    {
        return $this->assertionConsumerServiceURL;
    }

    public function setAssertionConsumerServiceIndex(?int $assertionConsumerServiceIndex): static
    {
        $this->assertionConsumerServiceIndex = null !== $assertionConsumerServiceIndex
            ? intval((string) $assertionConsumerServiceIndex)
            : null;

        return $this;
    }

    public function getAssertionConsumerServiceIndex(): ?int
    {
        return $this->assertionConsumerServiceIndex;
    }

    public function serialize(DOMNode $parent, SerializationContext $context): void
    {
        $result = $this->createElement('AuthnRequest', SamlConstants::NS_PROTOCOL, $parent, $context);

        parent::serialize($result, $context);

        $this->attributesToXml([
                'ForceAuthn', 'IsPassive', 'ProtocolBinding', 'AssertionConsumerServiceIndex',
                'AssertionConsumerServiceURL', 'AttributeConsumingServiceIndex', 'ProviderName',
            ], $result);

        $this->singleElementsToXml(['Subject', 'NameIDPolicy', 'Conditions'], $result, $context);

        // must be last in order signature to include them all
        $this->singleElementsToXml(['Signature'], $result, $context);
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'AuthnRequest', SamlConstants::NS_PROTOCOL);

        parent::deserialize($node, $context);

        $this->attributesFromXml($node, [
            'ForceAuthn', 'IsPassive', 'ProtocolBinding', 'AssertionConsumerServiceIndex',
            'AssertionConsumerServiceURL', 'AttributeConsumingServiceIndex', 'ProviderName',
        ]);

        $this->singleElementsFromXml($node, $context, [
            'Subject' => ['saml', Subject::class],
            'NameIDPolicy' => ['samlp', NameIDPolicy::class],
            'Conditions' => ['saml', Conditions::class],
        ]);
    }
}
