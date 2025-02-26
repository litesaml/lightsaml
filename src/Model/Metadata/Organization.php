<?php

namespace LightSaml\Model\Metadata;

use DOMElement;
use DOMNode;
use LightSaml\Error\LightSamlXmlException;
use LightSaml\Model\AbstractSamlModel;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LightSaml\SamlConstants;

class Organization extends AbstractSamlModel
{
    /** @var string */
    protected $organizationName;

    /** @var string */
    protected $organizationDisplayName;

    /** @var string */
    protected $organizationURL;

    protected $lang = 'en-US';

    /**
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @param string $lang
     *
     * @return Organization
     */
    public function setLang($lang)
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * @param string $organizationDisplayName
     *
     * @return Organization
     */
    public function setOrganizationDisplayName($organizationDisplayName)
    {
        $this->organizationDisplayName = (string) $organizationDisplayName;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrganizationDisplayName()
    {
        return $this->organizationDisplayName;
    }

    /**
     * @param string $organizationName
     *
     * @return Organization
     */
    public function setOrganizationName($organizationName)
    {
        $this->organizationName = (string) $organizationName;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrganizationName()
    {
        return $this->organizationName;
    }

    /**
     * @param string $organizationURL
     *
     * @return Organization
     */
    public function setOrganizationURL($organizationURL)
    {
        $this->organizationURL = (string) $organizationURL;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrganizationURL()
    {
        return $this->organizationURL;
    }

    /**
     * @return void
     */
    public function serialize(DOMNode $parent, SerializationContext $context)
    {
        if (!$this->lang) {
            throw new LightSamlXmlException('Lang is required');
        }

        $result = $this->createElement('Organization', SamlConstants::NS_METADATA, $parent, $context);

        $elements = ['OrganizationName', 'OrganizationDisplayName', 'OrganizationURL'];
        $this->singleElementsToXml(
            $elements,
            $result,
            $context,
            SamlConstants::NS_METADATA
        );

        /** @var DOMNode $node */
        foreach ($result->childNodes as $node) {
            if ($node instanceof DOMElement && in_array($node->tagName, $elements, true)) {
                $node->setAttribute('xml:lang', $this->lang);
            }
        }
    }

    public function deserialize(DOMNode $node, DeserializationContext $context)
    {
        $this->checkXmlNodeName($node, 'Organization', SamlConstants::NS_METADATA);

        $this->singleElementsFromXml($node, $context, [
            'OrganizationName' => ['md', null],
            'OrganizationDisplayName' => ['md', null],
            'OrganizationURL' => ['md', null],
        ]);
    }
}
