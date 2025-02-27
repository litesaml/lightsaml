<?php

namespace LightSaml\Model\Metadata;

use DOMComment;
use DOMNode;
use Exception;
use InvalidArgumentException;
use LightSaml\Error\LightSamlXmlException;
use LightSaml\Model\AbstractSamlModel;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\SamlElementInterface;
use LightSaml\SamlConstants;
use LogicException;

abstract class Metadata extends AbstractSamlModel
{
    /**
     * @param string $path
     *
     * @return EntitiesDescriptor|EntityDescriptor
     */
    public static function fromFile($path)
    {
        $deserializatonContext = new DeserializationContext();
        $xml = file_get_contents($path);

        return self::fromXML($xml, $deserializatonContext);
    }

    /**
     * @param string $xml
     *
     * @return EntityDescriptor|EntitiesDescriptor
     *
     * @throws Exception
     */
    public static function fromXML($xml, DeserializationContext $context)
    {
        if (false == is_string($xml)) {
            throw new InvalidArgumentException('Expecting string');
        }

        $context->getDocument()->loadXML($xml);

        $node = $context->getDocument()->firstChild;
        while ($node && $node instanceof DOMComment) {
            $node = $node->nextSibling;
        }
        if (!$node instanceof DOMNode) {
            throw new LightSamlXmlException('Empty XML');
        }

        if (SamlConstants::NS_METADATA !== $node->namespaceURI) {
            throw new LightSamlXmlException(sprintf("Invalid namespace '%s' of the root XML element, expected '%s'", $node->namespaceURI, SamlConstants::NS_METADATA));
        }

        $map = [
            'EntityDescriptor' => EntityDescriptor::class,
            'EntitiesDescriptor' => EntitiesDescriptor::class,
        ];

        $rootElementName = $node->localName;

        if (array_key_exists($rootElementName, $map)) {
            $class = $map[$rootElementName];
            if ($class !== '0') {
                /** @var SamlElementInterface $result */
                $result = new $class();
            } else {
                throw new LogicException('Deserialization of %s root element is not implemented');
            }
        } else {
            throw new LightSamlXmlException(sprintf("Unknown SAML metadata '%s'", $rootElementName));
        }

        $result->deserialize($node, $context);

        return $result;
    }
}
