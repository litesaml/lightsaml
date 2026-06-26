<?php

namespace LightSaml\Model\Assertion;

use DOMNode;
use LightSaml\Model\AbstractSamlModel;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LightSaml\SamlConstants;

class Subject extends AbstractSamlModel
{
    /** @var NameID */
    protected $nameId;

    /** @var SubjectConfirmation[] */
    protected $subjectConfirmation = [];

    
    public function setNameID(?NameID $nameId = null): static
    {
        $this->nameId = $nameId;

        return $this;
    }

    public function getNameID(): ?\LightSaml\Model\Assertion\NameID
    {
        return $this->nameId;
    }

    public function addSubjectConfirmation(SubjectConfirmation $subjectConfirmation): static
    {
        $this->subjectConfirmation[] = $subjectConfirmation;

        return $this;
    }

    /**
     * @return SubjectConfirmation[]
     */
    public function getAllSubjectConfirmations(): array
    {
        return $this->subjectConfirmation;
    }

    public function getFirstSubjectConfirmation(): ?\LightSaml\Model\Assertion\SubjectConfirmation
    {
        if (is_array($this->subjectConfirmation) && isset($this->subjectConfirmation[0])) {
            return $this->subjectConfirmation[0];
        }

        return null;
    }

    /**
     * Returns array of <SubjectConfirmation> containing a Method of urn:oasis:names:tc:SAML:2.0:cm:bearer.
     *
     * @return SubjectConfirmation[]
     */
    public function getBearerConfirmations(): array
    {
        $result = [];
        if ($this->getAllSubjectConfirmations()) {
            foreach ($this->getAllSubjectConfirmations() as $confirmation) {
                if (SamlConstants::CONFIRMATION_METHOD_BEARER == $confirmation->getMethod()) {
                    $result[] = $confirmation;
                    break;
                }
            }
        }

        return $result;
    }

    public function serialize(DOMNode $parent, SerializationContext $context): void
    {
        $result = $this->createElement('Subject', SamlConstants::NS_ASSERTION, $parent, $context);

        $this->singleElementsToXml(['NameID'], $result, $context);
        $this->manyElementsToXml($this->getAllSubjectConfirmations(), $result, $context, null);
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'Subject', SamlConstants::NS_ASSERTION);

        $this->singleElementsFromXml($node, $context, [
            'NameID' => ['saml', NameID::class],
        ]);

        $this->manyElementsFromXml(
            $node,
            $context,
            'SubjectConfirmation',
            'saml',
            SubjectConfirmation::class,
            'addSubjectConfirmation'
        );
    }
}
