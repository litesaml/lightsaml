<?php

namespace LightSaml\Model\Metadata;

use DOMElement;
use DOMNode;
use LightSaml\Context\Model\DeserializationContext;
use LightSaml\Context\Model\SerializationContext;
use LightSaml\Error\LightSamlXmlException;
use LightSaml\Model\AbstractSamlModel;
use LightSaml\SamlConstants;

class Organization extends AbstractSamlModel
{
    protected string $organizationName;

    protected string $organizationDisplayName;

    protected string $organizationURL;

    protected string $lang = 'en-US';

    public function getLang(): string
    {
        return $this->lang;
    }

    public function setLang(string $lang): static
    {
        $this->lang = $lang;

        return $this;
    }

    public function setOrganizationDisplayName(string $organizationDisplayName): static
    {
        $this->organizationDisplayName = $organizationDisplayName;

        return $this;
    }

    public function getOrganizationDisplayName(): string
    {
        return $this->organizationDisplayName;
    }

    public function setOrganizationName(string $organizationName): static
    {
        $this->organizationName = $organizationName;

        return $this;
    }

    public function getOrganizationName(): string
    {
        return $this->organizationName;
    }

    public function setOrganizationURL(string $organizationURL): static
    {
        $this->organizationURL = $organizationURL;

        return $this;
    }

    public function getOrganizationURL(): string
    {
        return $this->organizationURL;
    }

    public function serialize(DOMNode $parent, SerializationContext $context): void
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

    public function deserialize(DOMNode $node, DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'Organization', SamlConstants::NS_METADATA);

        $this->singleElementsFromXml($node, $context, [
            'OrganizationName' => ['md', null],
            'OrganizationDisplayName' => ['md', null],
            'OrganizationURL' => ['md', null],
        ]);
    }
}
