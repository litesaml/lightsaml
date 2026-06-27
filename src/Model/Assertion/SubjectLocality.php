<?php

namespace LightSaml\Model\Assertion;

use DOMNode;
use LightSaml\Context\Model\DeserializationContext;
use LightSaml\Context\Model\SerializationContext;
use LightSaml\Model\AbstractSamlModel;
use LightSaml\SamlConstants;

class SubjectLocality extends AbstractSamlModel
{
    protected ?string $address = null;

    protected ?string $dnsName = null;

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setDNSName(string $dnsName): static
    {
        $this->dnsName = $dnsName;

        return $this;
    }

    public function getDNSName(): ?string
    {
        return $this->dnsName;
    }

    public function serialize(DOMNode $parent, SerializationContext $context): void
    {
        $result = $this->createElement('SubjectLocality', SamlConstants::NS_ASSERTION, $parent, $context);

        $this->attributesToXml(['Address', 'DNSName'], $result);
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'SubjectLocality', SamlConstants::NS_ASSERTION);

        $this->attributesFromXml($node, ['Address', 'DNSName']);
    }
}
