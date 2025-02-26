<?php

namespace LightSaml\Model\Assertion;

use DateTime;
use DOMNode;
use LightSaml\Helper;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LightSaml\SamlConstants;

class AuthnStatement extends AbstractStatement
{
    /**
     * @var int|null
     */
    protected $authnInstant;

    /**
     * @var int|null
     */
    protected $sessionNotOnOrAfter;

    /**
     * @var string|null
     */
    protected $sessionIndex;

    /**
     * @var AuthnContext
     */
    protected $authnContext;

    /**
     * @var SubjectLocality
     */
    protected $subjectLocality;

    /**
     * @return AuthnStatement
     */
    public function setAuthnContext(AuthnContext $authnContext)
    {
        $this->authnContext = $authnContext;

        return $this;
    }

    /**
     * @return AuthnContext
     */
    public function getAuthnContext()
    {
        return $this->authnContext;
    }

    /**
     * @param int|string|DateTime $authnInstant
     *
     * @return AuthnStatement
     */
    public function setAuthnInstant($authnInstant)
    {
        $this->authnInstant = Helper::getTimestampFromValue($authnInstant);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getAuthnInstantTimestamp()
    {
        return $this->authnInstant;
    }

    /**
     * @return string|null
     */
    public function getAuthnInstantString()
    {
        if ($this->authnInstant) {
            return Helper::time2string($this->authnInstant);
        }

        return;
    }

    /**
     * @return DateTime|null
     */
    public function getAuthnInstantDateTime()
    {
        if ($this->authnInstant) {
            return new DateTime('@' . $this->authnInstant);
        }

        return;
    }

    /**
     * @param string|null $sessionIndex
     *
     * @return AuthnStatement
     */
    public function setSessionIndex($sessionIndex)
    {
        $this->sessionIndex = $sessionIndex;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSessionIndex()
    {
        return $this->sessionIndex;
    }

    /**
     * @param int|string|DateTime $sessionNotOnOrAfter
     *
     * @return AuthnStatement
     */
    public function setSessionNotOnOrAfter($sessionNotOnOrAfter)
    {
        $this->sessionNotOnOrAfter = Helper::getTimestampFromValue($sessionNotOnOrAfter);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getSessionNotOnOrAfterTimestamp()
    {
        return $this->sessionNotOnOrAfter;
    }

    /**
     * @return string|null
     */
    public function getSessionNotOnOrAfterString()
    {
        if ($this->sessionNotOnOrAfter) {
            return Helper::time2string($this->sessionNotOnOrAfter);
        }

        return;
    }

    /**
     * @return DateTime|null
     */
    public function getSessionNotOnOrAfterDateTime()
    {
        if ($this->sessionNotOnOrAfter) {
            return new DateTime('@' . $this->sessionNotOnOrAfter);
        }

        return;
    }

    /**
     * @param SubjectLocality $subjectLocality
     *
     * @return AuthnStatement
     */
    public function setSubjectLocality($subjectLocality)
    {
        $this->subjectLocality = $subjectLocality;

        return $this;
    }

    /**
     * @return SubjectLocality
     */
    public function getSubjectLocality()
    {
        return $this->subjectLocality;
    }

    /**
     * @return void
     */
    public function serialize(DOMNode $parent, SerializationContext $context)
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

    public function deserialize(DOMNode $node, DeserializationContext $context)
    {
        $this->checkXmlNodeName($node, 'AuthnStatement', SamlConstants::NS_ASSERTION);

        $this->attributesFromXml($node, ['AuthnInstant', 'SessionNotOnOrAfter', 'SessionIndex']);

        $this->singleElementsFromXml($node, $context, [
            'SubjectLocality' => ['saml', SubjectLocality::class],
            'AuthnContext' => ['saml', AuthnContext::class],
        ]);
    }
}
