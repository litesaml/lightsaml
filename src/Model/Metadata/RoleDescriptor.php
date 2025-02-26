<?php

namespace LightSaml\Model\Metadata;

use DateTime;
use DOMNode;
use InvalidArgumentException;
use LightSaml\Helper;
use LightSaml\Model\AbstractSamlModel;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LightSaml\Model\XmlDSig\Signature;
use LightSaml\SamlConstants;

abstract class RoleDescriptor extends AbstractSamlModel
{
    /** @var string|null */
    protected $id;

    /** @var int|null */
    protected $validUntil;

    /** @var string|null */
    protected $cacheDuration;

    /** @var string */
    protected $protocolSupportEnumeration = SamlConstants::PROTOCOL_SAML2;

    /** @var string|null */
    protected $errorURL;

    /** @var Signature[]|null */
    protected $signatures;

    /** @var KeyDescriptor[]|null */
    protected $keyDescriptors;

    /** @var Organization[]|null */
    protected $organizations;

    /** @var ContactPerson[]|null */
    protected $contactPersons;

    /**
     * @param string|null $cacheDuration
     *
     * @throws InvalidArgumentException
     *
     * @return RoleDescriptor
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
     * @return RoleDescriptor
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
     * @param string|null $errorURL
     *
     * @return RoleDescriptor
     */
    public function setErrorURL($errorURL)
    {
        $this->errorURL = (string) $errorURL;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getErrorURL()
    {
        return $this->errorURL;
    }

    /**
     * @param string|null $id
     *
     * @return RoleDescriptor
     */
    public function setID($id)
    {
        $this->id = (string) $id;

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
     * @return RoleDescriptor
     */
    public function addKeyDescriptor(KeyDescriptor $keyDescriptor)
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
    public function getAllKeyDescriptors()
    {
        return $this->keyDescriptors;
    }

    /**
     * @param string $use
     *
     * @return KeyDescriptor[]
     */
    public function getAllKeyDescriptorsByUse($use)
    {
        $result = [];
        foreach ($this->getAllKeyDescriptors() as $kd) {
            if ($kd->getUse() == $use) {
                $result[] = $kd;
            }
        }

        return $result;
    }

    /**
     * @param string|null $use
     *
     * @return KeyDescriptor|null
     */
    public function getFirstKeyDescriptor($use = null)
    {
        if ($this->getAllKeyDescriptors()) {
            foreach ($this->getAllKeyDescriptors() as $kd) {
                if (null == $use || $kd->getUse() == $use) {
                    return $kd;
                }
            }
        }

        return;
    }

    /**
     * @return RoleDescriptor
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
     * @param string $protocolSupportEnumeration
     *
     * @return RoleDescriptor
     */
    public function setProtocolSupportEnumeration($protocolSupportEnumeration)
    {
        $this->protocolSupportEnumeration = (string) $protocolSupportEnumeration;

        return $this;
    }

    /**
     * @return string
     */
    public function getProtocolSupportEnumeration()
    {
        return $this->protocolSupportEnumeration;
    }

    /**
     * @return RoleDescriptor
     */
    public function addSignature(Signature $signature)
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
    public function getAllSignatures()
    {
        return $this->signatures;
    }

    /**
     * @param int|null $validUntil
     *
     * @return RoleDescriptor
     */
    public function setValidUntil($validUntil)
    {
        $this->validUntil = Helper::getTimestampFromValue($validUntil);

        return $this;
    }

    /**
     * @return string
     */
    public function getValidUntilString()
    {
        if ($this->validUntil) {
            return Helper::time2string($this->validUntil);
        }

        return;
    }

    /**
     * @return int
     */
    public function getValidUntilTimestamp()
    {
        return $this->validUntil;
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
     * @return void
     */
    public function serialize(DOMNode $parent, SerializationContext $context)
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

    public function deserialize(DOMNode $node, DeserializationContext $context)
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
            Signature::class,
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
