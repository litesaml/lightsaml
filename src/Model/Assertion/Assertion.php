<?php

namespace LightSaml\Model\Assertion;

use DateTime;
use DOMNode;
use InvalidArgumentException;
use LightSaml\Helper;
use LightSaml\Model\AbstractSamlModel;
use LightSaml\Context\Model\DeserializationContext;
use LightSaml\Context\Model\SerializationContext;
use LightSaml\Model\XmlDSig\Signature;
use LightSaml\Model\XmlDSig\SignatureXmlReader;
use LightSaml\SamlConstants;

class Assertion extends AbstractSamlModel
{
    protected ?string $id = null;

    protected ?string $version = SamlConstants::VERSION_20;

    protected ?int $issueInstant = null;

    protected ?Issuer $issuer = null;

    protected ?Signature $signature = null;

    protected ?Subject $subject = null;

    protected ?Conditions $conditions = null;

    /** @var AbstractStatement[]|AuthnStatement[]|AttributeStatement[] */
    protected array $items = [];

    public function equals(string $nameId, ?string $format): bool
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
        return $this->getSubject()->getNameID()->getFormat() === $format;
    }

    public function hasSessionIndex(string $sessionIndex): bool
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

    public function hasAnySessionIndex(): bool
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

    public function setConditions(?Conditions $conditions = null): static
    {
        $this->conditions = $conditions;

        return $this;
    }

    public function getConditions(): ?Conditions
    {
        return $this->conditions;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setIssueInstant(int|string|DateTime $issueInstant): static
    {
        $this->issueInstant = Helper::getTimestampFromValue($issueInstant);

        return $this;
    }

    public function getIssueInstantTimestamp(): ?int
    {
        return $this->issueInstant;
    }

    public function getIssueInstantString(): ?string
    {
        if ($this->issueInstant) {
            return Helper::time2string($this->issueInstant);
        }

        return null;
    }

    public function getIssueInstantDateTime(): ?DateTime
    {
        if ($this->issueInstant) {
            return new DateTime('@' . $this->issueInstant);
        }

        return null;
    }

    public function setIssuer(?Issuer $issuer = null): static
    {
        $this->issuer = $issuer;

        return $this;
    }

    public function getIssuer(): ?Issuer
    {
        return $this->issuer;
    }

    public function setSignature(?Signature $signature = null): static
    {
        $this->signature = $signature;

        return $this;
    }

    public function getSignature(): ?Signature
    {
        return $this->signature;
    }

    public function setSubject(Subject $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getSubject(): ?Subject
    {
        return $this->subject;
    }

    public function setVersion(?string $version): static
    {
        $this->version = $version;

        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function addItem(AbstractStatement $statement): static
    {
        $this->items[] = $statement;

        return $this;
    }

    /** @return AbstractStatement[]|AttributeStatement[]|AuthnStatement[] */
    public function getAllItems(): array
    {
        return $this->items;
    }

    /**
     * @return AuthnStatement[]
     */
    public function getAllAuthnStatements(): array
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
    public function getAllAttributeStatements(): array
    {
        $result = [];
        foreach ($this->items as $item) {
            if ($item instanceof AttributeStatement) {
                $result[] = $item;
            }
        }

        return $result;
    }

    public function getFirstAttributeStatement(): ?AttributeStatement
    {
        foreach ($this->items as $item) {
            if ($item instanceof AttributeStatement) {
                return $item;
            }
        }

        return null;
    }

    public function getFirstAuthnStatement(): ?AuthnStatement
    {
        foreach ($this->items as $item) {
            if ($item instanceof AuthnStatement) {
                return $item;
            }
        }

        return null;
    }

    public function hasBearerSubject(): bool
    {
        return $this->getAllAuthnStatements() && $this->getSubject() && $this->getSubject()->getBearerConfirmations();
    }

    protected function prepareForXml(): void
    {
        if (false == $this->getId()) {
            $this->setId(Helper::generateID());
        }
        if (false == $this->getIssueInstantTimestamp()) {
            $this->setIssueInstant(time());
        }
    }

    public function serialize(DOMNode $parent, SerializationContext $context): void
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

    public function deserialize(DOMNode $node, DeserializationContext $context): void
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
