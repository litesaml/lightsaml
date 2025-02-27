<?php

namespace LightSaml\Model\Metadata;

use DateTime;
use DOMNode;
use InvalidArgumentException;
use LightSaml\Helper;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LightSaml\Model\XmlDSig\Signature;
use LightSaml\Model\XmlDSig\SignatureXmlReader;
use LightSaml\SamlConstants;

class EntityDescriptor extends Metadata
{
    /** @var int|null */
    protected $validUntil;

    /** @var string|null */
    protected $cacheDuration;

    /** @var string|null */
    protected $id;

    /** @var Signature|null */
    protected $signature;

    /** @var IdpSsoDescriptor[]|SpSsoDescriptor[] */
    protected $items;

    /** @var Organization[]|null */
    protected $organizations;

    /** @var ContactPerson[]|null */
    protected $contactPersons;

    /**
     * @param string $filename
     *
     * @return EntityDescriptor
     */
    public static function load($filename)
    {
        return self::loadXml(file_get_contents($filename));
    }

    /**
     * @param string $xml
     *
     * @return EntityDescriptor
     */
    public static function loadXml($xml)
    {
        $context = new DeserializationContext();
        $context->getDocument()->loadXML($xml);
        $ed = new self();
        $ed->deserialize($context->getDocument(), $context);

        return $ed;
    }

    /**
     * @param string|null $entityID
     */
    public function __construct(protected $entityID = null, array $items = [])
    {
        $this->items = $items;
    }

    /**
     * @return EntityDescriptor
     */
    public function addContactPerson(ContactPerson $contactPerson)
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
    public function getAllContactPersons()
    {
        return $this->contactPersons;
    }

    /**
     * @return ContactPerson|null
     */
    public function getFirstContactPerson()
    {
        if (is_array($this->contactPersons) && isset($this->contactPersons[0])) {
            return $this->contactPersons[0];
        }

        return;
    }

    /**
     * @return EntityDescriptor
     */
    public function addOrganization(Organization $organization)
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
    public function getAllOrganizations()
    {
        return $this->organizations;
    }

    /**
     * @return Organization|null
     */
    public function getFirstOrganization()
    {
        if (is_array($this->organizations) && isset($this->organizations[0])) {
            return $this->organizations[0];
        }

        return;
    }

    /**
     * @param string|null $cacheDuration
     *
     * @throws InvalidArgumentException
     *
     * @return EntityDescriptor
     */
    public function setCacheDuration($cacheDuration)
    {
        Helper::validateDurationString($cacheDuration);

        $this->cacheDuration = $cacheDuration;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCacheDuration()
    {
        return $this->cacheDuration;
    }

    /**
     * @param string $entityID
     *
     * @return EntityDescriptor
     */
    public function setEntityID($entityID)
    {
        $this->entityID = (string) $entityID;

        return $this;
    }

    /**
     * @return string
     */
    public function getEntityID()
    {
        return $this->entityID;
    }

    /**
     * @param string|null $id
     *
     * @return EntityDescriptor
     */
    public function setID($id)
    {
        $this->id = null !== $id ? (string) $id : null;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * @param IdpSsoDescriptor|SpSsoDescriptor $item
     *
     * @throws InvalidArgumentException
     *
     * @return EntityDescriptor
     */
    public function addItem($item)
    {
        if (
            false == $item instanceof IdpSsoDescriptor
            && false == $item instanceof SpSsoDescriptor
        ) {
            throw new InvalidArgumentException('EntityDescriptor item must be IdpSsoDescriptor or SpSsoDescriptor');
        }

        if (false == is_array($this->items)) {
            $this->items = [];
        }

        $this->items[] = $item;

        return $this;
    }

    /**
     * @return IdpSsoDescriptor[]|SpSsoDescriptor[]|SSODescriptor[]
     */
    public function getAllItems()
    {
        return $this->items;
    }

    /**
     * @return IdpSsoDescriptor[]
     */
    public function getAllIdpSsoDescriptors()
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
    public function getAllSpSsoDescriptors()
    {
        $result = [];
        foreach ($this->getAllItems() as $item) {
            if ($item instanceof SpSsoDescriptor) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * @return IdpSsoDescriptor|null
     */
    public function getFirstIdpSsoDescriptor()
    {
        foreach ($this->getAllItems() as $item) {
            if ($item instanceof IdpSsoDescriptor) {
                return $item;
            }
        }

        return;
    }

    /**
     * @return SpSsoDescriptor|null
     */
    public function getFirstSpSsoDescriptor()
    {
        foreach ($this->getAllItems() as $item) {
            if ($item instanceof SpSsoDescriptor) {
                return $item;
            }
        }

        return;
    }

    /**
     * @param Signature|null $signature
     *
     * @return EntityDescriptor
     */
    public function setSignature(Signature $signature)
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
     * @param int $validUntil
     *
     * @return EntityDescriptor
     */
    public function setValidUntil($validUntil)
    {
        $this->validUntil = Helper::getTimestampFromValue($validUntil);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getValidUntilTimestamp()
    {
        return $this->validUntil;
    }

    /**
     * @return string|null
     */
    public function getValidUntilString()
    {
        if ($this->validUntil) {
            return Helper::time2string($this->validUntil);
        }

        return;
    }

    /**
     * @return DateTime|null
     */
    public function getValidUntilDateTime()
    {
        if ($this->validUntil) {
            return new DateTime('@' . $this->validUntil);
        }

        return;
    }

    /**
     * @return array|KeyDescriptor[]
     */
    public function getAllIdpKeyDescriptors()
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
     * @return array|KeyDescriptor[]
     */
    public function getAllSpKeyDescriptors()
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
    public function getAllEndpoints()
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

    /**
     * @return void
     */
    public function serialize(DOMNode $parent, SerializationContext $context)
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

    public function deserialize(DOMNode $node, DeserializationContext $context)
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
