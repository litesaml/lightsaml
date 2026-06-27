<?php

namespace LightSaml\Model\Metadata;

use DateTime;
use DOMNode;
use InvalidArgumentException;
use LightSaml\Context\Model\DeserializationContext;
use LightSaml\Context\Model\SerializationContext;
use LightSaml\Helper;
use LightSaml\Model\AbstractSamlModel;
use LightSaml\Model\XmlDSig\Signature;
use LightSaml\Model\XmlDSig\SignatureXmlReader;
use LightSaml\SamlConstants;

abstract class RoleDescriptor extends AbstractSamlModel
{
    protected ?string $id = null;

    protected ?int $validUntil = null;

    protected ?string $cacheDuration = null;

    protected string $protocolSupportEnumeration = SamlConstants::PROTOCOL_SAML2;

    protected ?string $errorURL = null;

    /** @var Signature[]|null */
    protected ?array $signatures = null;

    /** @var KeyDescriptor[]|null */
    protected ?array $keyDescriptors = null;

    /** @var Organization[]|null */
    protected ?array $organizations = null;

    /** @var ContactPerson[]|null */
    protected ?array $contactPersons = null;

    /**
     * @throws InvalidArgumentException
     */
    public function setCacheDuration(?string $cacheDuration): static
    {
        if ($cacheDuration !== null) {
            Helper::validateDurationString($cacheDuration);
        }

        $this->cacheDuration = $cacheDuration;

        return $this;
    }

    public function getCacheDuration(): ?string
    {
        return $this->cacheDuration;
    }

    public function addContactPerson(ContactPerson $contactPerson): static
    {
        if (false == is_array($this->contactPersons)) {
            $this->contactPersons = [];
        }
        $this->contactPersons[] = $contactPerson;

        return $this;
    }

    /**
     * @return ContactPerson[]|null
     */
    public function getAllContactPersons(): ?array
    {
        return $this->contactPersons;
    }

    public function setErrorURL(?string $errorURL): static
    {
        $this->errorURL = (string) $errorURL;

        return $this;
    }

    public function getErrorURL(): ?string
    {
        return $this->errorURL;
    }

    public function setID(?string $id): static
    {
        $this->id = (string) $id;

        return $this;
    }

    public function getID(): ?string
    {
        return $this->id;
    }

    public function addKeyDescriptor(KeyDescriptor $keyDescriptor): static
    {
        if (false == is_array($this->keyDescriptors)) {
            $this->keyDescriptors = [];
        }
        $this->keyDescriptors[] = $keyDescriptor;

        return $this;
    }

    /**
     * @return KeyDescriptor[]|null
     */
    public function getAllKeyDescriptors(): ?array
    {
        return $this->keyDescriptors;
    }

    /**
     * @return KeyDescriptor[]
     */
    public function getAllKeyDescriptorsByUse(string $use): array
    {
        $result = [];

        if ($this->getAllKeyDescriptors()) {
            foreach ($this->getAllKeyDescriptors() as $kd) {
                if ($kd->getUse() == $use) {
                    $result[] = $kd;
                }
            }
        }

        return $result;
    }

    public function getFirstKeyDescriptor(?string $use = null): ?KeyDescriptor
    {
        if ($this->getAllKeyDescriptors()) {
            foreach ($this->getAllKeyDescriptors() as $kd) {
                if (null == $use || $kd->getUse() == $use) {
                    return $kd;
                }
            }
        }

        return null;
    }

    public function addOrganization(Organization $organization): static
    {
        if (false == is_array($this->organizations)) {
            $this->organizations = [];
        }
        $this->organizations[] = $organization;

        return $this;
    }

    /**
     * @return Organization[]|null
     */
    public function getAllOrganizations(): ?array
    {
        return $this->organizations;
    }

    public function setProtocolSupportEnumeration(string $protocolSupportEnumeration): static
    {
        $this->protocolSupportEnumeration = $protocolSupportEnumeration;

        return $this;
    }

    public function getProtocolSupportEnumeration(): string
    {
        return $this->protocolSupportEnumeration;
    }

    public function addSignature(Signature $signature): static
    {
        if (false == is_array($this->signatures)) {
            $this->signatures = [];
        }
        $this->signatures[] = $signature;

        return $this;
    }

    /**
     * @return Signature[]|null
     */
    public function getAllSignatures(): ?array
    {
        return $this->signatures;
    }

    public function setValidUntil(int|string|DateTime $validUntil): static
    {
        $this->validUntil = Helper::getTimestampFromValue($validUntil);

        return $this;
    }

    public function getValidUntilString(): ?string
    {
        if ($this->validUntil) {
            return Helper::time2string($this->validUntil);
        }

        return null;
    }

    public function getValidUntilTimestamp(): int
    {
        return $this->validUntil;
    }

    public function getValidUntilDateTime(): ?DateTime
    {
        if ($this->validUntil) {
            return new DateTime('@' . $this->validUntil);
        }

        return null;
    }

    public function serialize(DOMNode $parent, SerializationContext $context): void
    {
        $this->attributesToXml(
            ['protocolSupportEnumeration', 'ID', 'validUntil', 'cacheDuration', 'errorURL'],
            $parent
        );

        $this->manyElementsToXml($this->getAllSignatures(), $parent, $context, null);
        $this->manyElementsToXml($this->getAllKeyDescriptors(), $parent, $context, null);
        $this->manyElementsToXml($this->getAllOrganizations(), $parent, $context, null);
        $this->manyElementsToXml($this->getAllContactPersons(), $parent, $context, null);
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): void
    {
        $this->attributesFromXml(
            $node,
            ['protocolSupportEnumeration', 'ID', 'validUntil', 'cacheDuration', 'errorURL']
        );

        $this->manyElementsFromXml(
            $node,
            $context,
            'Signature',
            'ds',
            SignatureXmlReader::class,
            'addSignature'
        );
        $this->manyElementsFromXml(
            $node,
            $context,
            'KeyDescriptor',
            'md',
            KeyDescriptor::class,
            'addKeyDescriptor'
        );
        $this->manyElementsFromXml(
            $node,
            $context,
            'Organization',
            'md',
            Organization::class,
            'addOrganization'
        );
        $this->manyElementsFromXml(
            $node,
            $context,
            'ContactPerson',
            'md',
            ContactPerson::class,
            'addContactPerson'
        );
    }
}
