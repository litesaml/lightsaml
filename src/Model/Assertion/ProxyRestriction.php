<?php

namespace LightSaml\Model\Assertion;

use DOMNode;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LightSaml\SamlConstants;

class ProxyRestriction extends AbstractCondition
{
    /**
     * @param int      $count
     * @param string[] $audience
     */
    public function __construct(protected $count = null, protected ?array $audience = null)
    {
    }

    /**
     * @param string $audience
     *
     * @return ProxyRestriction
     */
    public function addAudience($audience)
    {
        if (false == is_array($this->audience)) {
            $this->audience = [];
        }
        $this->audience[] = (string) $audience;

        return $this;
    }

    /**
     * @return string[]|null
     */
    public function getAllAudience()
    {
        return $this->audience;
    }

    /**
     * @param int|null $count
     *
     * @return ProxyRestriction
     */
    public function setCount($count)
    {
        $this->count = null !== $count ? intval($count) : null;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @return void
     */
    public function serialize(DOMNode $parent, SerializationContext $context)
    {
        $result = $this->createElement('ProxyRestriction', SamlConstants::NS_ASSERTION, $parent, $context);

        $this->attributesToXml(['count'], $result);

        $this->manyElementsToXml($this->getAllAudience(), $result, $context, 'Audience');
    }

    public function deserialize(DOMNode $node, DeserializationContext $context)
    {
        $this->checkXmlNodeName($node, 'ProxyRestriction', SamlConstants::NS_ASSERTION);

        $this->attributesFromXml($node, ['count']);

        $this->manyElementsFromXml($node, $context, 'Audience', 'saml', null, 'addAudience');
    }
}
