<?php

namespace LightSaml\Model\Metadata;

use DOMNode;
use LightSaml\Model\Assertion\Attribute;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LightSaml\SamlConstants;

class IdpSsoDescriptor extends SSODescriptor
{
    /** @var bool|null */
    protected $wantAuthnRequestsSigned;

    /** @var SingleSignOnService[]|null */
    protected $singleSignOnServices;

    /** @var Attribute[]|null */
    protected $attributes;

    /**
     * @param bool|null $wantAuthnRequestsSigned
     *
     * @return IdpSsoDescriptor
     */
    public function setWantAuthnRequestsSigned($wantAuthnRequestsSigned)
    {
        $this->wantAuthnRequestsSigned = filter_var($wantAuthnRequestsSigned, FILTER_VALIDATE_BOOLEAN, ['flags' => FILTER_NULL_ON_FAILURE]);

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getWantAuthnRequestsSigned()
    {
        return $this->wantAuthnRequestsSigned;
    }

    /**
     * @return IdpSsoDescriptor
     */
    public function addSingleSignOnService(SingleSignOnService $singleSignOnService)
    {
        if (false == is_array($this->singleSignOnServices)) {
            $this->singleSignOnServices = [];
        }
        $this->singleSignOnServices[] = $singleSignOnService;

        return $this;
    }

    /**
     * @return SingleSignOnService[]|null
     */
    public function getAllSingleSignOnServices()
    {
        return $this->singleSignOnServices;
    }

    /**
     * @param string $url
     *
     * @return SingleSignOnService[]
     */
    public function getAllSingleSignOnServicesByUrl($url)
    {
        $result = [];
        foreach ($this->getAllSingleSignOnServices() as $svc) {
            if ($svc->getLocation() == $url) {
                $result[] = $svc;
            }
        }

        return $result;
    }

    /**
     * @param string $binding
     *
     * @return SingleSignOnService[]
     */
    public function getAllSingleSignOnServicesByBinding($binding)
    {
        $result = [];
        foreach ($this->getAllSingleSignOnServices() as $svc) {
            if ($svc->getBinding() == $binding) {
                $result[] = $svc;
            }
        }

        return $result;
    }

    /**
     * @param string|null $binding
     *
     * @return SingleSignOnService|null
     */
    public function getFirstSingleSignOnService($binding = null)
    {
        foreach ($this->getAllSingleSignOnServices() as $svc) {
            if (null == $binding || $svc->getBinding() == $binding) {
                return $svc;
            }
        }

        return;
    }

    /**
     * @return IdpSsoDescriptor
     */
    public function addAttribute(Attribute $attribute)
    {
        if (false == is_array($this->attributes)) {
            $this->attributes = [];
        }
        $this->attributes[] = $attribute;

        return $this;
    }

    /**
     * @return Attribute[]|null
     */
    public function getAllAttributes()
    {
        return $this->attributes;
    }

    public function serialize(DOMNode $parent, SerializationContext $context)
    {
        $result = $this->createElement('IDPSSODescriptor', SamlConstants::NS_METADATA, $parent, $context);

        parent::serialize($result, $context);

        $this->attributesToXml(['WantAuthnRequestsSigned'], $result);

        if ($this->getAllSingleSignOnServices()) {
            foreach ($this->getAllSingleSignOnServices() as $object) {
                $object->serialize($result, $context);
            }
        }
        if ($this->getAllAttributes()) {
            foreach ($this->getAllAttributes() as $object) {
                $object->serialize($result, $context);
            }
        }
    }

    public function deserialize(DOMNode $node, DeserializationContext $context)
    {
        $this->checkXmlNodeName($node, 'IDPSSODescriptor', SamlConstants::NS_METADATA);

        parent::deserialize($node, $context);

        $this->attributesFromXml($node, ['WantAuthnRequestsSigned']);

        $this->singleSignOnServices = [];
        $this->manyElementsFromXml(
            $node,
            $context,
            'SingleSignOnService',
            'md',
            SingleSignOnService::class,
            'addSingleSignOnService'
        );

        $this->attributes = [];
        $this->manyElementsFromXml(
            $node,
            $context,
            'Attribute',
            'saml',
            Attribute::class,
            'addAttribute'
        );
    }
}
