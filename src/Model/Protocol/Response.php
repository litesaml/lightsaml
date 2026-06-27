<?php

namespace LightSaml\Model\Protocol;

use DOMNode;
use InvalidArgumentException;
use LightSaml\Model\Assertion\Assertion;
use LightSaml\Model\Assertion\EncryptedAssertionReader;
use LightSaml\Model\Assertion\EncryptedElement;
use LightSaml\Context\Model\DeserializationContext;
use LightSaml\Context\Model\SerializationContext;
use LightSaml\SamlConstants;

class Response extends StatusResponse
{
    /** @var Assertion[] */
    protected array $assertions = [];

    /** @var EncryptedElement[] */
    protected array $encryptedAssertions = [];

    /**
     * @return Assertion[]
     */
    public function getAllAssertions(): array
    {
        return $this->assertions;
    }

    public function getFirstAssertion(): ?Assertion
    {
        if (is_array($this->assertions) && isset($this->assertions[0])) {
            return $this->assertions[0];
        }

        return null;
    }

    /**
     * @return EncryptedElement[]
     */
    public function getAllEncryptedAssertions(): array
    {
        return $this->encryptedAssertions;
    }

    public function getFirstEncryptedAssertion(): ?EncryptedElement
    {
        if (is_array($this->encryptedAssertions) && isset($this->encryptedAssertions[0])) {
            return $this->encryptedAssertions[0];
        }

        return null;
    }

    /**
     * Returns assertions with <AuthnStatement> and <Subject> with at least one <SubjectConfirmation>
     * element containing a Method of urn:oasis:names:tc:SAML:2.0:cm:bearer.
     *
     * @return Assertion[]
     */
    public function getBearerAssertions(): array
    {
        $result = [];
        if ($this->getAllAssertions()) {
            foreach ($this->getAllAssertions() as $assertion) {
                if ($assertion->hasBearerSubject()) {
                    $result[] = $assertion;
                }
            } // foreach assertions
        }

        return $result;
    }

    public function addAssertion(Assertion $assertion): static
    {
        $this->assertions[] = $assertion;

        return $this;
    }

    public function removeAssertion(Assertion $removedAssertion): static
    {
        $arr = [];
        $hasThatAssertion = false;
        foreach ($this->getAllAssertions() as $assertion) {
            if ($assertion !== $removedAssertion) {
                $arr[] = $assertion;
            } else {
                $hasThatAssertion = true;
            }
        }

        if (false === $hasThatAssertion) {
            throw new InvalidArgumentException('Response does not have assertion specified to be removed');
        }

        return $this;
    }

    public function addEncryptedAssertion(EncryptedElement $encryptedAssertion): static
    {
        $this->encryptedAssertions[] = $encryptedAssertion;

        return $this;
    }

    public function serialize(DOMNode $parent, SerializationContext $context): void
    {
        $result = $this->createElement('samlp:Response', SamlConstants::NS_PROTOCOL, $parent, $context);

        parent::serialize($result, $context);

        $this->manyElementsToXml($this->getAllAssertions(), $result, $context, null);
        $this->manyElementsToXml($this->getAllEncryptedAssertions(), $result, $context, null);

        // must be done here at the end and not in a base class where declared in order to include signing of the elements added here
        $this->singleElementsToXml(['Signature'], $result, $context);
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'Response', SamlConstants::NS_PROTOCOL);

        parent::deserialize($node, $context);

        $this->assertions = [];
        $this->manyElementsFromXml(
            $node,
            $context,
            'Assertion',
            'saml',
            Assertion::class,
            'addAssertion'
        );

        $this->encryptedAssertions = [];
        $this->manyElementsFromXml(
            $node,
            $context,
            'EncryptedAssertion',
            'saml',
            EncryptedAssertionReader::class,
            'addEncryptedAssertion'
        );
    }
}
