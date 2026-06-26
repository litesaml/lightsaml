<?php

namespace LightSaml\Model;

use DOMComment;
use DOMDocument;
use DOMElement;
use DOMNode;
use LightSaml\Error\LightSamlXmlException;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LogicException;

abstract class AbstractSamlModel implements SamlElementInterface
{
    
    protected function createElement(string $name, ?string $namespace, DOMNode $parent, SerializationContext $context): \DOMElement
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

    /**
     * @param array|string[] $names
     */
    protected function singleElementsToXml(array $names, DOMNode $parent, SerializationContext $context, ?string $namespace = null)
    {
        foreach ($names as $name) {
            $this->oneElementToXml($name, $parent, $context, $namespace);
        }
    }

    /**
     *
     * @throws LogicException
     */
    protected function manyElementsToXml(?array $value, DOMNode $node, SerializationContext $context, ?string $nodeName = null, ?string $namespaceUri = null)
    {
        if (false == $value) {
            return;
        }

        if (false == is_array($value)) {
            throw new LogicException('value must be array or null');
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
     *
     * @throws LogicException
     */
    protected function manyElementsFromXml(DOMElement $node, DeserializationContext $context, string $nodeName, ?string $namespacePrefix, ?string $class, string $methodName)
    {
        $query = $namespacePrefix ? sprintf('%s:%s', $namespacePrefix, $nodeName) : $nodeName;

        foreach ($context->getXpath()->query($query, $node) as $xml) {
            /* @var \DOMElement $xml */
            if ($class !== null && $class !== '' && $class !== '0') {
                /** @var SamlElementInterface $object */
                $object = new $class();
                if (false == $object instanceof SamlElementInterface) {
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

    /**
     * @param array|string[] $names
     */
    protected function attributesToXml(array $names, DOMElement $element)
    {
        foreach ($names as $name) {
            $this->singleAttributeToXml($name, $element);
        }
    }

    protected function checkXmlNodeName(DOMNode &$node, string $expectedName, string $expectedNamespaceUri)
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

    protected function singleAttributeFromXml(DOMElement $node, string $attributeName)
    {
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
    protected function oneElementFromXml(DOMElement $node, DeserializationContext $context, string $elementName, ?string $class, string $namespacePrefix)
    {
        $query = $namespacePrefix !== '' && $namespacePrefix !== '0' ? sprintf('./%s:%s', $namespacePrefix, $elementName) : sprintf('./%s', $elementName);
        $arr = $context->getXpath()->query($query, $node);
        $value = $arr->length > 0 ? $arr->item(0) : null;

        if ($value) {
            $setter = 'set' . $elementName;
            if (false == method_exists($this, $setter)) {
                throw new LogicException(sprintf("Unable to find setter for element '%s' in class '%s'", $elementName, static::class));
            }

            if ($class !== null && $class !== '' && $class !== '0') {
                /** @var AbstractSamlModel $object */
                $object = new $class();
                if (false == $object instanceof SamlElementInterface) {
                    throw new LogicException(sprintf("Specified class '%s' for element '%s' must implement SamlElementInterface", $class, $elementName));
                }

                $object->deserialize($value, $context);
            } else {
                $object = $value->textContent;
            }

            $this->{$setter}($object);
        }
    }

    /**
     * @param array $options elementName=>class
     */
    protected function singleElementsFromXml(DOMElement $node, DeserializationContext $context, array $options)
    {
        foreach ($options as $elementName => $info) {
            $this->oneElementFromXml($node, $context, $elementName, $info[1], $info[0]);
        }
    }

    protected function attributesFromXml(DOMElement $node, array $attributeNames)
    {
        foreach ($attributeNames as $attributeName) {
            $this->singleAttributeFromXml($node, $attributeName);
        }
    }

    /**
     *
     * @return mixed
     * @throws LogicException
     */
    private function getPropertyValue(string $name)
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
