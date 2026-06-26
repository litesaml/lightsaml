<?php

namespace LightSaml\Model\Assertion;

use DOMNode;
use LightSaml\Model\AbstractSamlModel;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LightSaml\SamlConstants;

class SubjectConfirmation extends AbstractSamlModel
{
    /** @var string */
    protected $method;

    /** @var NameID|null */
    protected $nameId;

    /** @var EncryptedElement|null */
    protected $encryptedId;

    /** @var SubjectConfirmationData|null */
    protected $subjectConfirmationData;

    public function setMethod(string $method): static
    {
        $this->method = $method;

        return $this;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setEncryptedId(?EncryptedElement $encryptedId = null): static
    {
        $this->encryptedId = $encryptedId;

        return $this;
    }

    public function getEncryptedId(): ?\LightSaml\Model\Assertion\EncryptedElement
    {
        return $this->encryptedId;
    }

    public function setNameID(?NameID $nameId = null): static
    {
        $this->nameId = $nameId;

        return $this;
    }

    public function getNameID(): ?\LightSaml\Model\Assertion\NameID
    {
        return $this->nameId;
    }

    public function setSubjectConfirmationData(?SubjectConfirmationData $subjectConfirmationData = null): static
    {
        $this->subjectConfirmationData = $subjectConfirmationData;

        return $this;
    }

    public function getSubjectConfirmationData(): ?\LightSaml\Model\Assertion\SubjectConfirmationData
    {
        return $this->subjectConfirmationData;
    }

    public function serialize(DOMNode $parent, SerializationContext $context): void
    {
        $result = $this->createElement('SubjectConfirmation', SamlConstants::NS_ASSERTION, $parent, $context);

        $this->attributesToXml(['Method'], $result);

        $this->singleElementsToXml(
            ['NameID', 'EncryptedID', 'SubjectConfirmationData'],
            $result,
            $context
        );
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'SubjectConfirmation', SamlConstants::NS_ASSERTION);

        $this->attributesFromXml($node, ['Method']);

        $this->singleElementsFromXml($node, $context, [
            'NameID' => ['saml', NameID::class],
            'EncryptedID' => ['saml', 'LightSaml\Model\Assertion\EncryptedID'],
            'SubjectConfirmationData' => ['saml', SubjectConfirmationData::class],
        ]);
    }
}
