<?php

namespace LightSaml\Model\Metadata;

use DOMNode;
use LightSaml\Model\AbstractSamlModel;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LightSaml\SamlConstants;

class ContactPerson extends AbstractSamlModel
{
    public const TYPE_TECHNICAL = 'technical';
    public const TYPE_SUPPORT = 'support';
    public const TYPE_ADMINISTRATIVE = 'administrative';
    public const TYPE_BILLING = 'billing';
    public const TYPE_OTHER = 'other';

    /** @var string */
    protected $contactType;

    /** @var string|null */
    protected $company;

    /** @var string|null */
    protected $givenName;

    /** @var string|null */
    protected $surName;

    /** @var string|null */
    protected $emailAddress;

    /** @var string|null */
    protected $telephoneNumber;

    public function setContactType(string $contactType): static
    {
        $this->contactType = $contactType;

        return $this;
    }

    public function getContactType(): string
    {
        return $this->contactType;
    }

    public function setCompany(?string $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setEmailAddress(?string $emailAddress): static
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setGivenName(?string $givenName): static
    {
        $this->givenName = $givenName;

        return $this;
    }

    public function getGivenName(): ?string
    {
        return $this->givenName;
    }

    public function setSurName(?string $surName): static
    {
        $this->surName = $surName;

        return $this;
    }

    public function getSurName(): ?string
    {
        return $this->surName;
    }

    public function setTelephoneNumber(?string $telephoneNumber): static
    {
        $this->telephoneNumber = $telephoneNumber;

        return $this;
    }

    public function getTelephoneNumber(): ?string
    {
        return $this->telephoneNumber;
    }

    public function serialize(DOMNode $parent, SerializationContext $context): void
    {
        $result = $this->createElement('ContactPerson', SamlConstants::NS_METADATA, $parent, $context);

        $this->attributesToXml(['contactType'], $result);

        $this->singleElementsToXml(
            ['Company', 'GivenName', 'SurName', 'EmailAddress', 'TelephoneNumber'],
            $result,
            $context,
            SamlConstants::NS_METADATA
        );
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'ContactPerson', SamlConstants::NS_METADATA);

        $this->attributesFromXml($node, ['contactType']);

        $this->singleElementsFromXml($node, $context, [
            'Company' => ['md', null],
            'GivenName' => ['md', null],
            'SurName' => ['md', null],
            'EmailAddress' => ['md', null],
            'TelephoneNumber' => ['md', null],
        ]);
    }
}
