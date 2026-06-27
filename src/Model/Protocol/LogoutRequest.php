<?php

namespace LightSaml\Model\Protocol;

use DateTime;
use DOMNode;
use LightSaml\Helper;
use LightSaml\Model\Assertion\NameID;
use LightSaml\Context\Model\DeserializationContext;
use LightSaml\Context\Model\SerializationContext;
use LightSaml\SamlConstants;

class LogoutRequest extends AbstractRequest
{
    protected ?string $reason = null;

    protected ?int $notOnOrAfter = null;

    protected NameID $nameID;

    protected ?string $sessionIndex = null;

    public function setNameID(NameID $nameID): static
    {
        $this->nameID = $nameID;

        return $this;
    }

    public function getNameID(): NameID
    {
        return $this->nameID;
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

    public function setReason(?string $reason): static
    {
        $this->reason = (string) $reason;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setSessionIndex(?string $sessionIndex): static
    {
        $this->sessionIndex = (string) $sessionIndex;

        return $this;
    }

    public function getSessionIndex(): ?string
    {
        return $this->sessionIndex;
    }

    public function serialize(DOMNode $parent, SerializationContext $context): void
    {
        $result = $this->createElement('LogoutRequest', SamlConstants::NS_PROTOCOL, $parent, $context);

        parent::serialize($result, $context);

        $this->attributesToXml(['Reason', 'NotOnOrAfter'], $result);

        $this->singleElementsToXml(['NameID', 'SessionIndex'], $result, $context, SamlConstants::NS_PROTOCOL);

        // must be last in order signature to include them all
        $this->singleElementsToXml(['Signature'], $result, $context);
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'LogoutRequest', SamlConstants::NS_PROTOCOL);

        parent::deserialize($node, $context);

        $this->attributesFromXml($node, ['Reason', 'NotOnOrAfter']);

        $this->singleElementsFromXml($node, $context, [
            'NameID' => ['saml', NameID::class],
            'SessionIndex' => ['samlp', null],
        ]);
    }
}
