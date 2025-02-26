<?php

namespace LightSaml\Model\Protocol;

use DOMNode;
use InvalidArgumentException;
use LightSaml\Model\Assertion\Assertion;
use LightSaml\Model\Assertion\EncryptedAssertionReader;
use LightSaml\Model\Assertion\EncryptedElement;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LightSaml\SamlConstants;

class Response extends StatusResponse
{
    /** @var Assertion[] */
    protected $assertions = [];

    /** @var EncryptedElement[] */
    protected $encryptedAssertions = [];

    /**
     * @return Assertion[]
     */
    public function getAllAssertions()
    {
        return $this->assertions;
    }

    /**
     * @return Assertion|null
     */
    public function getFirstAssertion()
    {
        if (is_array($this->assertions) && isset($this->assertions[0])) {
            return $this->assertions[0];
        }

        return;
    }

    /**
     * @return EncryptedElement[]
     */
    public function getAllEncryptedAssertions()
    {
        return $this->encryptedAssertions;
    }

    /**
     * @return EncryptedElement|null
     */
    public function getFirstEncryptedAssertion()
    {
        if (is_array($this->encryptedAssertions) && isset($this->encryptedAssertions[0])) {
            return $this->encryptedAssertions[0];
        }

        return;
    }

    /**
     * Returns assertions with <AuthnStatement> and <Subject> with at least one <SubjectConfirmation>
     * element containing a Method of urn:oasis:names:tc:SAML:2.0:cm:bearer.
     *
     * @return Assertion[]
     */
    public function getBearerAssertions()
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

    /**
     * @return Response
     */
    public function addAssertion(Assertion $assertion)
    {
        $this->assertions[] = $assertion;

        return $this;
    }

    /**
     * @return Response
     */
    public function removeAssertion(Assertion $removedAssertion)
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

    /**
     * @return Response
     */
    public function addEncryptedAssertion(EncryptedElement $encryptedAssertion)
    {
        $this->encryptedAssertions[] = $encryptedAssertion;

        return $this;
    }

    public function serialize(DOMNode $parent, SerializationContext $context)
    {
        $result = $this->createElement('samlp:Response', SamlConstants::NS_PROTOCOL, $parent, $context);

        parent::serialize($result, $context);

        $this->manyElementsToXml($this->getAllAssertions(), $result, $context, null);
        $this->manyElementsToXml($this->getAllEncryptedAssertions(), $result, $context, null);

        // must be done here at the end and not in a base class where declared in order to include signing of the elements added here
        $this->singleElementsToXml(['Signature'], $result, $context);
    }

    public function deserialize(DOMNode $node, DeserializationContext $context)
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
