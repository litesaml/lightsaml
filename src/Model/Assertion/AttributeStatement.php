<?php

namespace LightSaml\Model\Assertion;

use DOMNode;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LightSaml\SamlConstants;

class AttributeStatement extends AbstractStatement
{
    /** @var Attribute[] */
    protected array $attributes = [];

    public function addAttribute(Attribute $attribute): static
    {
        $this->attributes[] = $attribute;

        return $this;
    }

    /**
     * @return Attribute[]
     */
    public function getAllAttributes(): array
    {
        return $this->attributes;
    }

    
    public function getFirstAttributeByName(string $name): ?Attribute
    {
        if (is_array($this->getAllAttributes())) {
            foreach ($this->getAllAttributes() as $attribute) {
                if (null == $name || $attribute->getName() == $name) {
                    return $attribute;
                }
            }
        }

        return null;
    }

    public function serialize(DOMNode $parent, SerializationContext $context): void
    {
        $result = $this->createElement('AttributeStatement', SamlConstants::NS_ASSERTION, $parent, $context);

        $this->manyElementsToXml($this->getAllAttributes(), $result, $context, null);
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'AttributeStatement', SamlConstants::NS_ASSERTION);

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
