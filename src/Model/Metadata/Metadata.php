<?php

namespace LightSaml\Model\Metadata;

use DOMComment;
use DOMNode;
use Exception;
use LightSaml\Error\LightSamlXmlException;
use LightSaml\Model\AbstractSamlModel;
use LightSaml\Context\Model\DeserializationContext;
use LightSaml\SamlConstants;

abstract class Metadata extends AbstractSamlModel
{
    public static function fromFile(string $path): EntitiesDescriptor|EntityDescriptor
    {
        $deserializatonContext = new DeserializationContext();
        $xml = file_get_contents($path);

        return self::fromXML($xml, $deserializatonContext);
    }

    /**
     *
     *
     * @throws Exception
     */
    public static function fromXML(string $xml, DeserializationContext $context): EntityDescriptor|EntitiesDescriptor
    {
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

        if (!array_key_exists($rootElementName, $map)) {
            throw new LightSamlXmlException(sprintf("Unknown SAML metadata '%s'", $rootElementName));
        }

        $class = $map[$rootElementName];
        /** @var EntityDescriptor|EntitiesDescriptor $result */
        $result = new $class();

        $result->deserialize($node, $context);

        return $result;
    }
}
