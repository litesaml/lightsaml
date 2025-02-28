<?php

namespace LightSaml\Model\Assertion;

use DateTime;
use DOMNode;
use InvalidArgumentException;
use LightSaml\Helper;
use LightSaml\Model\AbstractSamlModel;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LightSaml\Model\XmlDSig\Signature;
use LightSaml\Model\XmlDSig\SignatureXmlReader;
use LightSaml\SamlConstants;

class Assertion extends AbstractSamlModel
{
    //region Attributes

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $version = SamlConstants::VERSION_20;

    /**
     * @var int
     */
    protected $issueInstant;

    //endregion

    //region Elements

    /**
     * @var Issuer
     */
    protected $issuer;

    /**
     * @var Signature|null
     */
    protected $signature;

    /**
     * @var Subject|null
     */
    protected $subject;

    /**
     * @var Conditions|null
     */
    protected $conditions;

    /**
     * @var array|AbstractStatement[]|AuthnStatement[]|AttributeStatement[]
     */
    protected $items = [];

    //endregion

    /**
     * Core 3.3.4 Processing rules.
     *
     * @param string      $nameId
     * @param string|null $format
     *
     * @return bool
     */
    public function equals($nameId, $format)
    {
        if (false == $this->getSubject()) {
            return false;
        }

        if (false == $this->getSubject()->getNameID()) {
            return false;
        }

        if ($this->getSubject()->getNameID()->getValue() != $nameId) {
            return false;
        }
        return $this->getSubject()->getNameID()->getFormat() == $format;
    }

    /**
     * @param string $sessionIndex
     *
     * @return bool
     */
    public function hasSessionIndex($sessionIndex)
    {
        if (null == $this->getAllAuthnStatements()) {
            return false;
        }

        foreach ($this->getAllAuthnStatements() as $authnStatement) {
            if ($authnStatement->getSessionIndex() == $sessionIndex) {
                return true;
            }
        }

        return false;
    }

    public function hasAnySessionIndex()
    {
        if (false == $this->getAllAuthnStatements()) {
            return false;
        }

        foreach ($this->getAllAuthnStatements() as $authnStatement) {
            if ($authnStatement->getSessionIndex()) {
                return true;
            }
        }

        return false;
    }

    //region Getters & Setters

    /**
     * @return Assertion
     */
    public function setConditions(?Conditions $conditions = null)
    {
        $this->conditions = $conditions;

        return $this;
    }

    /**
     * @return Conditions|null
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @param string $id
     *
     * @return Assertion
     */
    public function setId($id)
    {
        $this->id = (string) $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string|int|DateTime $issueInstant
     *
     * @throws InvalidArgumentException
     *
     * @return Assertion
     */
    public function setIssueInstant($issueInstant)
    {
        $this->issueInstant = Helper::getTimestampFromValue($issueInstant);

        return $this;
    }

    /**
     * @return int
     */
    public function getIssueInstantTimestamp()
    {
        return $this->issueInstant;
    }

    /**
     * @return string
     */
    public function getIssueInstantString()
    {
        if ($this->issueInstant) {
            return Helper::time2string($this->issueInstant);
        }

        return;
    }

    /**
     * @return string
     */
    public function getIssueInstantDateTime()
    {
        if ($this->issueInstant) {
            return new DateTime('@' . $this->issueInstant);
        }

        return;
    }

    /**
     *
     * @return Assertion
     */
    public function setIssuer(?Issuer $issuer = null)
    {
        $this->issuer = $issuer;

        return $this;
    }

    /**
     * @return Issuer
     */
    public function getIssuer()
    {
        return $this->issuer;
    }

    /**
     *
     * @return Assertion
     */
    public function setSignature(?Signature $signature = null)
    {
        $this->signature = $signature;

        return $this;
    }

    /**
     * @return Signature|null
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * @return Assertion
     */
    public function setSubject(Subject $subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return Subject
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $version
     *
     * @return Assertion
     */
    public function setVersion($version)
    {
        $this->version = (string) $version;

        return $this;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return Assertion
     */
    public function addItem(AbstractStatement $statement)
    {
        $this->items[] = $statement;

        return $this;
    }

    /**
     * @return AbstractStatement[]|AttributeStatement[]|AuthnStatement[]|array
     */
    public function getAllItems()
    {
        return $this->items;
    }

    /**
     * @return AuthnStatement[]
     */
    public function getAllAuthnStatements()
    {
        $result = [];
        foreach ($this->items as $item) {
            if ($item instanceof AuthnStatement) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * @return AttributeStatement[]
     */
    public function getAllAttributeStatements()
    {
        $result = [];
        foreach ($this->items as $item) {
            if ($item instanceof AttributeStatement) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * @return AttributeStatement|null
     */
    public function getFirstAttributeStatement()
    {
        foreach ($this->items as $item) {
            if ($item instanceof AttributeStatement) {
                return $item;
            }
        }

        return;
    }

    /**
     * @return AuthnStatement|null
     */
    public function getFirstAuthnStatement()
    {
        foreach ($this->items as $item) {
            if ($item instanceof AuthnStatement) {
                return $item;
            }
        }

        return;
    }

    //endregion

    /**
     * @return bool
     */
    public function hasBearerSubject()
    {
        return $this->getAllAuthnStatements() && $this->getSubject() && $this->getSubject()->getBearerConfirmations();
    }

    protected function prepareForXml()
    {
        if (false == $this->getId()) {
            $this->setId(Helper::generateID());
        }
        if (false == $this->getIssueInstantTimestamp()) {
            $this->setIssueInstant(time());
        }
    }

    /**
     * @return void
     */
    public function serialize(DOMNode $parent, SerializationContext $context)
    {
        $this->prepareForXml();

        $result = $this->createElement('Assertion', SamlConstants::NS_ASSERTION, $parent, $context);

        $this->attributesToXml(['ID', 'Version', 'IssueInstant'], $result);

        $this->singleElementsToXml(
            ['Issuer', 'Subject', 'Conditions'],
            $result,
            $context
        );

        foreach ($this->items as $item) {
            $item->serialize($result, $context);
        }

        // must be added at the end
        $this->singleElementsToXml(['Signature'], $result, $context);
    }

    public function deserialize(DOMNode $node, DeserializationContext $context)
    {
        $this->checkXmlNodeName($node, 'Assertion', SamlConstants::NS_ASSERTION);

        $this->attributesFromXml($node, ['ID', 'Version', 'IssueInstant']);

        $this->singleElementsFromXml($node, $context, [
            'Issuer' => ['saml', Issuer::class],
            'Subject' => ['saml', Subject::class],
            'Conditions' => ['saml', Conditions::class],
        ]);

        $this->manyElementsFromXml(
            $node,
            $context,
            'AuthnStatement',
            'saml',
            AuthnStatement::class,
            'addItem'
        );

        $this->manyElementsFromXml(
            $node,
            $context,
            'AttributeStatement',
            'saml',
            AttributeStatement::class,
            'addItem'
        );

        $this->singleElementsFromXml($node, $context, [
            'Signature' => ['ds', SignatureXmlReader::class],
        ]);
    }
}
