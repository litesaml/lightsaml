<?php

namespace LightSaml\Model\Assertion;

use DOMNode;
use LightSaml\Model\AbstractSamlModel;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LightSaml\SamlConstants;

class Attribute extends AbstractSamlModel
{
    /** @var string */
    protected $nameFormat;

    /** @var string */
    protected $friendlyName;

    /** @var string[] */
    protected $attributeValue;

    /**
     * @param string|string[] $value
     */
    public function __construct(protected ?string $name = null, string|array|null $value = null)
    {
        if ($value) {
            $this->attributeValue = is_array($value) ? $value : [$value];
        }
    }

    public function addAttributeValue(string $attributeValue): static
    {
        if (false == is_array($this->attributeValue)) {
            $this->attributeValue = [];
        }
        $this->attributeValue[] = $attributeValue;

        return $this;
    }

    /**
     * @param string[]|string $attributeValue
     */
    public function setAttributeValue(array|string $attributeValue): static
    {
        if (false == is_array($attributeValue)) {
            $attributeValue = [$attributeValue];
        }
        $this->attributeValue = $attributeValue;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getAllAttributeValues(): ?array
    {
        return $this->attributeValue;
    }

    public function getFirstAttributeValue(): ?string
    {
        $arr = $this->attributeValue;

        return array_shift($arr);
    }

    public function setFriendlyName(string $friendlyName): static
    {
        $this->friendlyName = $friendlyName;

        return $this;
    }

    public function getFriendlyName(): ?string
    {
        return $this->friendlyName;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setNameFormat(string $nameFormat): static
    {
        $this->nameFormat = $nameFormat;

        return $this;
    }

    public function getNameFormat(): ?string
    {
        return $this->nameFormat;
    }

    public function serialize(DOMNode $parent, SerializationContext $context): void
    {
        $result = $this->createElement('Attribute', SamlConstants::NS_ASSERTION, $parent, $context);

        $this->attributesToXml(['Name', 'NameFormat', 'FriendlyName'], $result);

        $this->manyElementsToXml($this->getAllAttributeValues(), $result, $context, 'AttributeValue', SamlConstants::NS_ASSERTION);
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'Attribute', SamlConstants::NS_ASSERTION);

        $this->attributesFromXml($node, ['Name', 'NameFormat', 'FriendlyName']);

        $this->attributeValue = [];
        $this->manyElementsFromXml($node, $context, 'AttributeValue', 'saml', null, 'addAttributeValue');
    }
}
