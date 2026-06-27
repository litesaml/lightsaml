<?php

namespace LightSaml\Model\Metadata;

use DateTime;
use DOMNode;
use InvalidArgumentException;
use LightSaml\Helper;
use LightSaml\Context\Model\DeserializationContext;
use LightSaml\Context\Model\SerializationContext;
use LightSaml\Model\XmlDSig\Signature;
use LightSaml\Model\XmlDSig\SignatureXmlReader;
use LightSaml\SamlConstants;

class EntityDescriptor extends Metadata
{
    protected ?int $validUntil = null;

    protected ?string $cacheDuration = null;

    protected ?string $id = null;

    protected ?Signature $signature = null;

    /** @var IdpSsoDescriptor[]|SpSsoDescriptor[] */
    protected array $items = [];

    /** @var Organization[]|null */
    protected ?array $organizations = null;

    /** @var ContactPerson[]|null */
    protected ?array $contactPersons = null;

    public static function load(string $filename): EntityDescriptor
    {
        return self::loadXml(file_get_contents($filename));
    }

    public static function loadXml(string $xml): self
    {
        $context = new DeserializationContext();
        $context->getDocument()->loadXML($xml);
        $ed = new self();
        $ed->deserialize($context->getDocument(), $context);

        return $ed;
    }

    /** @param IdpSsoDescriptor[]|SpSsoDescriptor[] $items */
    public function __construct(protected ?string $entityID = null, array $items = [])
    {
        $this->items = $items;
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

    public function getFirstContactPerson(): ?ContactPerson
    {
        if (is_array($this->contactPersons) && isset($this->contactPersons[0])) {
            return $this->contactPersons[0];
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

    public function getFirstOrganization(): ?Organization
    {
        if (is_array($this->organizations) && isset($this->organizations[0])) {
            return $this->organizations[0];
        }

        return null;
    }

    /**
     */
    public function setCacheDuration(string $cacheDuration): static
    {
        Helper::validateDurationString($cacheDuration);

        $this->cacheDuration = $cacheDuration;

        return $this;
    }

    public function getCacheDuration(): ?string
    {
        return $this->cacheDuration;
    }

    public function setEntityID(string $entityID): static
    {
        $this->entityID = $entityID;

        return $this;
    }

    public function getEntityID(): string
    {
        return $this->entityID;
    }

    public function setID(?string $id): static
    {
        $this->id = $id ?? null;

        return $this;
    }

    public function getID(): ?string
    {
        return $this->id;
    }

    /**
     */
    public function addItem(IdpSsoDescriptor|SpSsoDescriptor $item): static
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * @return IdpSsoDescriptor[]|SpSsoDescriptor[]|SSODescriptor[]
     */
    public function getAllItems(): array
    {
        return $this->items;
    }

    /**
     * @return IdpSsoDescriptor[]
     */
    public function getAllIdpSsoDescriptors(): array
    {
        $result = [];
        foreach ($this->getAllItems() as $item) {
            if ($item instanceof IdpSsoDescriptor) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * @return SpSsoDescriptor[]
     */
    public function getAllSpSsoDescriptors(): array
    {
        $result = [];
        foreach ($this->getAllItems() as $item) {
            if ($item instanceof SpSsoDescriptor) {
                $result[] = $item;
            }
        }

        return $result;
    }

    public function getFirstIdpSsoDescriptor(): ?IdpSsoDescriptor
    {
        foreach ($this->getAllItems() as $item) {
            if ($item instanceof IdpSsoDescriptor) {
                return $item;
            }
        }

        return null;
    }

    public function getFirstSpSsoDescriptor(): ?SpSsoDescriptor
    {
        foreach ($this->getAllItems() as $item) {
            if ($item instanceof SpSsoDescriptor) {
                return $item;
            }
        }

        return null;
    }

    public function setSignature(Signature $signature): static
    {
        $this->signature = $signature;

        return $this;
    }

    public function getSignature(): ?Signature
    {
        return $this->signature;
    }

    public function setValidUntil(int|string|DateTime $validUntil): static
    {
        $this->validUntil = Helper::getTimestampFromValue($validUntil);

        return $this;
    }

    public function getValidUntilTimestamp(): ?int
    {
        return $this->validUntil;
    }

    public function getValidUntilString(): ?string
    {
        if ($this->validUntil) {
            return Helper::time2string($this->validUntil);
        }

        return null;
    }

    public function getValidUntilDateTime(): ?DateTime
    {
        if ($this->validUntil) {
            return new DateTime('@' . $this->validUntil);
        }

        return null;
    }

    /**
     * @return KeyDescriptor[]
     */
    public function getAllIdpKeyDescriptors(): array
    {
        $result = [];
        foreach ($this->getAllIdpSsoDescriptors() as $idp) {
            foreach ($idp->getAllKeyDescriptors() as $key) {
                $result[] = $key;
            }
        }

        return $result;
    }

    /**
     * @return KeyDescriptor[]
     */
    public function getAllSpKeyDescriptors(): array
    {
        $result = [];
        foreach ($this->getAllSpSsoDescriptors() as $sp) {
            foreach ($sp->getAllKeyDescriptors() as $key) {
                $result[] = $key;
            }
        }

        return $result;
    }

    /**
     * @return EndpointReference[]
     */
    public function getAllEndpoints(): array
    {
        $result = [];
        foreach ($this->getAllIdpSsoDescriptors() as $idpSsoDescriptor) {
            foreach ($idpSsoDescriptor->getAllSingleSignOnServices() as $sso) {
                $result[] = new EndpointReference($this, $idpSsoDescriptor, $sso);
            }
            foreach ($idpSsoDescriptor->getAllSingleLogoutServices() as $slo) {
                $result[] = new EndpointReference($this, $idpSsoDescriptor, $slo);
            }
        }
        foreach ($this->getAllSpSsoDescriptors() as $spSsoDescriptor) {
            foreach ($spSsoDescriptor->getAllAssertionConsumerServices() as $acs) {
                $result[] = new EndpointReference($this, $spSsoDescriptor, $acs);
            }
            foreach ($spSsoDescriptor->getAllSingleLogoutServices() as $slo) {
                $result[] = new EndpointReference($this, $spSsoDescriptor, $slo);
            }
        }

        return $result;
    }

    public function serialize(DOMNode $parent, SerializationContext $context): void
    {
        $result = $this->createElement('EntityDescriptor', SamlConstants::NS_METADATA, $parent, $context);

        $this->attributesToXml(['entityID', 'validUntil', 'cacheDuration', 'ID'], $result);

        $this->manyElementsToXml($this->getAllItems(), $result, $context, null);
        if ($this->organizations) {
            $this->manyElementsToXml($this->organizations, $result, $context, null);
        }
        if ($this->contactPersons) {
            $this->manyElementsToXml($this->contactPersons, $result, $context, null);
        }

        $this->singleElementsToXml(['Signature'], $result, $context);
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'EntityDescriptor', SamlConstants::NS_METADATA);

        $this->attributesFromXml($node, ['entityID', 'validUntil', 'cacheDuration', 'ID']);

        $this->items = [];

        $this->manyElementsFromXml(
            $node,
            $context,
            'IDPSSODescriptor',
            'md',
            IdpSsoDescriptor::class,
            'addItem'
        );

        $this->manyElementsFromXml(
            $node,
            $context,
            'SPSSODescriptor',
            'md',
            SpSsoDescriptor::class,
            'addItem'
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

        $this->singleElementsFromXml($node, $context, [
            'Signature' => ['ds', SignatureXmlReader::class],
        ]);
    }
}
