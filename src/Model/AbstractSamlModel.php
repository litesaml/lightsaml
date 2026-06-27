<?php

namespace LightSaml\Model;

use DOMComment;
use DOMDocument;
use DOMElement;
use DOMNode;
use LightSaml\Context\Model\DeserializationContext;
use LightSaml\Context\Model\SerializationContext;
use LightSaml\Error\LightSamlXmlException;
use LogicException;

abstract class AbstractSamlModel implements SamlElementInterface
{
    protected function createElement(string $name, ?string $namespace, DOMNode $parent, SerializationContext $context): DOMElement
    {
        if ($namespace) {
            $result = $context->getDocument()->createElementNS($namespace, $name);
        } else {
            $result = $context->getDocument()->createElement($name);
        }
        $parent->appendChild($result);

        return $result;
    }

    /**
     *
     * @throws LogicException
     */
    private function oneElementToXml(string $name, DOMNode $parent, SerializationContext $context, ?string $namespace = null): void
    {
        $value = $this->getPropertyValue($name);
        if (null == $value) {
            return;
        }
        if ($value instanceof SamlElementInterface) {
            $value->serialize($parent, $context);
        } elseif (is_string($value)) {
            if ($namespace) {
                $node = $context->getDocument()->createElementNS($namespace, $name, $value);
            } else {
                $node = $context->getDocument()->createElement($name, $value);
            }
            $parent->appendChild($node);
        } else {
            throw new LogicException(sprintf("Element '%s' must implement SamlElementInterface or be a string", $name));
        }
    }

    /** @param array<int, string> $names */
    protected function singleElementsToXml(array $names, DOMNode $parent, SerializationContext $context, ?string $namespace = null): void
    {
        foreach ($names as $name) {
            $this->oneElementToXml($name, $parent, $context, $namespace);
        }
    }

    /**
     * @param array<int, mixed>|null $value
     *
     * @throws LogicException
     */
    protected function manyElementsToXml(?array $value, DOMNode $node, SerializationContext $context, ?string $nodeName = null, ?string $namespaceUri = null): void
    {
        if (!$value) {
            return;
        }

        foreach ($value as $object) {
            if ($object instanceof SamlElementInterface) {
                if ($nodeName) {
                    throw new LogicException('nodeName should not be specified when serializing array of SamlElementInterface');
                }
                $object->serialize($node, $context);
            } elseif ($nodeName) {
                if ($namespaceUri) {
                    $child = $context->getDocument()->createElementNS($namespaceUri, $nodeName, (string) $object);
                } else {
                    $child = $context->getDocument()->createElement($nodeName, (string) $object);
                }
                $node->appendChild($child);
            } else {
                throw new LogicException('Can handle only array of AbstractSamlModel or strings with nodeName parameter specified');
            }
        }
    }

    /**
     * @throws LogicException
     */
    protected function manyElementsFromXml(DOMNode $node, DeserializationContext $context, string $nodeName, ?string $namespacePrefix, ?string $class, string $methodName): void
    {
        $query = $namespacePrefix ? sprintf('%s:%s', $namespacePrefix, $nodeName) : $nodeName;

        foreach ($context->getXpath()->query($query, $node) as $xml) {
            if ($class !== null && $class !== '') {
                $object = new $class();
                if (!$object instanceof SamlElementInterface) {
                    throw new LogicException(sprintf("Node '%s' class '%s' must implement SamlElementInterface", $nodeName, $class));
                }
                $object->deserialize($xml, $context);
                $this->{$methodName}($object);
            } else {
                $object = $xml->textContent;
                $this->{$methodName}($object);
            }
        }
    }

    /**
     *
     * @throws LogicException
     *
     * @return bool True if property value is not empty and attribute was set to the element
     */
    protected function singleAttributeToXml(string $name, DOMElement $element): bool
    {
        $value = $this->getPropertyValue($name);
        if (null !== $value && '' !== $value) {
            if (is_bool($value)) {
                $element->setAttribute($name, $value ? 'true' : 'false');
            } else {
                $element->setAttribute($name, $value);
            }

            return true;
        }

        return false;
    }

    /** @param array<int, string> $names */
    protected function attributesToXml(array $names, DOMNode $element): void
    {
        if (!$element instanceof DOMElement) {
            throw new LightSamlXmlException(sprintf('Expected DOMElement, got %s', $element::class));
        }
        foreach ($names as $name) {
            $this->singleAttributeToXml($name, $element);
        }
    }

    protected function checkXmlNodeName(DOMNode &$node, string $expectedName, string $expectedNamespaceUri): void
    {
        if ($node instanceof DOMDocument) {
            $node = $node->firstChild;
        }
        while ($node && $node instanceof DOMComment) {
            $node = $node->nextSibling;
        }
        if (!$node instanceof DOMNode) {
            throw new LightSamlXmlException(sprintf("Unable to find expected '%s' xml node and '%s' namespace", $expectedName, $expectedNamespaceUri));
        } elseif ($node->localName != $expectedName || $node->namespaceURI != $expectedNamespaceUri) {
            throw new LightSamlXmlException(sprintf("Expected '%s' xml node and '%s' namespace but got node '%s' and namespace '%s'", $expectedName, $expectedNamespaceUri, $node->localName, $node->namespaceURI));
        }
    }

    protected function singleAttributeFromXml(DOMNode $node, string $attributeName): void
    {
        if (!$node instanceof DOMElement) {
            throw new LightSamlXmlException(sprintf('Expected DOMElement, got %s', $node::class));
        }
        $value = $node->getAttribute($attributeName);
        if ('' !== $value) {
            $setter = 'set' . $attributeName;
            if (method_exists($this, $setter)) {
                $this->{$setter}($value);
            }
        }
    }

    /**
     * @throws LogicException
     */
    protected function oneElementFromXml(DOMNode $node, DeserializationContext $context, string $elementName, ?string $class, string $namespacePrefix): void
    {
        $query = $namespacePrefix !== '' ? sprintf('./%s:%s', $namespacePrefix, $elementName) : sprintf('./%s', $elementName);
        $arr = $context->getXpath()->query($query, $node);
        $value = $arr->length > 0 ? $arr->item(0) : null;

        if ($value) {
            $setter = 'set' . $elementName;
            if (!method_exists($this, $setter)) {
                throw new LogicException(sprintf("Unable to find setter for element '%s' in class '%s'", $elementName, static::class));
            }

            if ($class !== null && $class !== '') {
                $object = new $class();
                if (!$object instanceof SamlElementInterface) {
                    throw new LogicException(sprintf("Specified class '%s' for element '%s' must implement SamlElementInterface", $class, $elementName));
                }

                $object->deserialize($value, $context);
            } else {
                $object = $value->textContent;
            }

            $this->{$setter}($object);
        }
    }

    /** @param array<string, array{string, class-string|null}> $options elementName => [namespacePrefix, class] */
    protected function singleElementsFromXml(DOMNode $node, DeserializationContext $context, array $options): void
    {
        foreach ($options as $elementName => $info) {
            $this->oneElementFromXml($node, $context, $elementName, $info[1], $info[0]);
        }
    }

    /** @param array<int, string> $attributeNames */
    protected function attributesFromXml(DOMNode $node, array $attributeNames): void
    {
        foreach ($attributeNames as $attributeName) {
            $this->singleAttributeFromXml($node, $attributeName);
        }
    }

    /**
     * @throws LogicException
     */
    private function getPropertyValue(string $name): mixed
    {
        if (false !== ($pos = strpos($name, ':'))) {
            $name = substr($name, $pos + 1);
        }
        $getter = 'get' . $name . 'String';
        if (false == method_exists($this, $getter)) {
            $getter = 'get' . $name;
        }
        if (false == method_exists($this, $getter)) {
            throw new LogicException(sprintf("Unable to find getter method for '%s' on '%s'", $name, static::class));
        }

        return $this->{$getter}();
    }
}
