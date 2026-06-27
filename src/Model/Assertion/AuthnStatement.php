<?php

namespace LightSaml\Model\Assertion;

use DateTime;
use DOMNode;
use LightSaml\Helper;
use LightSaml\Context\Model\DeserializationContext;
use LightSaml\Context\Model\SerializationContext;
use LightSaml\SamlConstants;

class AuthnStatement extends AbstractStatement
{
    protected ?int $authnInstant = null;

    protected ?int $sessionNotOnOrAfter = null;

    protected ?string $sessionIndex = null;

    protected ?AuthnContext $authnContext = null;

    protected ?SubjectLocality $subjectLocality = null;

    public function setAuthnContext(AuthnContext $authnContext): static
    {
        $this->authnContext = $authnContext;

        return $this;
    }

    public function getAuthnContext(): ?AuthnContext
    {
        return $this->authnContext;
    }

    public function setAuthnInstant(int|string|DateTime $authnInstant): static
    {
        $this->authnInstant = Helper::getTimestampFromValue($authnInstant);

        return $this;
    }

    public function getAuthnInstantTimestamp(): ?int
    {
        return $this->authnInstant;
    }

    public function getAuthnInstantString(): ?string
    {
        if ($this->authnInstant) {
            return Helper::time2string($this->authnInstant);
        }

        return null;
    }

    public function getAuthnInstantDateTime(): ?DateTime
    {
        if ($this->authnInstant) {
            return new DateTime('@' . $this->authnInstant);
        }

        return null;
    }

    public function setSessionIndex(?string $sessionIndex): static
    {
        $this->sessionIndex = $sessionIndex;

        return $this;
    }

    public function getSessionIndex(): ?string
    {
        return $this->sessionIndex;
    }

    public function setSessionNotOnOrAfter(int|string|DateTime $sessionNotOnOrAfter): static
    {
        $this->sessionNotOnOrAfter = Helper::getTimestampFromValue($sessionNotOnOrAfter);

        return $this;
    }

    public function getSessionNotOnOrAfterTimestamp(): ?int
    {
        return $this->sessionNotOnOrAfter;
    }

    public function getSessionNotOnOrAfterString(): ?string
    {
        if ($this->sessionNotOnOrAfter) {
            return Helper::time2string($this->sessionNotOnOrAfter);
        }

        return null;
    }

    public function getSessionNotOnOrAfterDateTime(): ?DateTime
    {
        if ($this->sessionNotOnOrAfter) {
            return new DateTime('@' . $this->sessionNotOnOrAfter);
        }

        return null;
    }

    public function setSubjectLocality(SubjectLocality $subjectLocality): static
    {
        $this->subjectLocality = $subjectLocality;

        return $this;
    }

    public function getSubjectLocality(): ?SubjectLocality
    {
        return $this->subjectLocality;
    }

    public function serialize(DOMNode $parent, SerializationContext $context): void
    {
        $result = $this->createElement('AuthnStatement', SamlConstants::NS_ASSERTION, $parent, $context);

        $this->attributesToXml(
            ['AuthnInstant', 'SessionNotOnOrAfter', 'SessionIndex'],
            $result
        );

        $this->singleElementsToXml(
            ['SubjectLocality', 'AuthnContext'],
            $result,
            $context
        );
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'AuthnStatement', SamlConstants::NS_ASSERTION);

        $this->attributesFromXml($node, ['AuthnInstant', 'SessionNotOnOrAfter', 'SessionIndex']);

        $this->singleElementsFromXml($node, $context, [
            'SubjectLocality' => ['saml', SubjectLocality::class],
            'AuthnContext' => ['saml', AuthnContext::class],
        ]);
    }
}
