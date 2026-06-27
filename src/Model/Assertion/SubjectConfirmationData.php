<?php

namespace LightSaml\Model\Assertion;

use DateTime;
use DOMNode;
use LightSaml\Helper;
use LightSaml\Model\AbstractSamlModel;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LightSaml\SamlConstants;

class SubjectConfirmationData extends AbstractSamlModel
{
    protected ?int $notBefore = null;

    protected ?int $notOnOrAfter = null;

    protected ?string $address = null;

    protected ?string $inResponseTo = null;

    protected ?string $recipient = null;

    public function setAddress(?string $address): static
    {
        $this->address = (string) $address;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setInResponseTo(?string $inResponseTo): static
    {
        $this->inResponseTo = (string) $inResponseTo;

        return $this;
    }

    public function getInResponseTo(): ?string
    {
        return $this->inResponseTo;
    }

    public function setNotBefore(int|string|DateTime $notBefore): static
    {
        $this->notBefore = Helper::getTimestampFromValue($notBefore);

        return $this;
    }

    public function getNotBeforeTimestamp(): ?int
    {
        return $this->notBefore;
    }

    public function getNotBeforeString(): ?string
    {
        if ($this->notBefore) {
            return Helper::time2string($this->notBefore);
        }

        return null;
    }

    public function getNotBeforeDateTime(): ?DateTime
    {
        if ($this->notBefore) {
            return new DateTime('@' . $this->notBefore);
        }

        return null;
    }

    public function setNotOnOrAfter(int|string|DateTime $notOnOrAfter): static
    {
        $this->notOnOrAfter = Helper::getTimestampFromValue($notOnOrAfter);

        return $this;
    }

    public function getNotOnOrAfterTimestamp(): ?int
    {
        return $this->notOnOrAfter;
    }

    public function getNotOnOrAfterString(): ?string
    {
        if ($this->notOnOrAfter) {
            return Helper::time2string($this->notOnOrAfter);
        }

        return null;
    }

    public function getNotOnOrAfterDateTime(): ?DateTime
    {
        if ($this->notOnOrAfter) {
            return new DateTime('@' . $this->notOnOrAfter);
        }

        return null;
    }

    public function setRecipient(?string $recipient): static
    {
        $this->recipient = (string) $recipient;

        return $this;
    }

    public function getRecipient(): ?string
    {
        return $this->recipient;
    }

    public function serialize(DOMNode $parent, SerializationContext $context): void
    {
        $result = $this->createElement('SubjectConfirmationData', SamlConstants::NS_ASSERTION, $parent, $context);

        $this->attributesToXml(
            ['InResponseTo', 'NotBefore', 'NotOnOrAfter', 'Address', 'Recipient'],
            $result
        );
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'SubjectConfirmationData', SamlConstants::NS_ASSERTION);

        $this->attributesFromXml($node, [
            'InResponseTo', 'NotBefore', 'NotOnOrAfter', 'Address', 'Recipient',
        ]);
    }
}
