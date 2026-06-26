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

    
    public function addAudience(string $audience): static
    {
        if (false == is_array($this->audience)) {
            $this->audience = [];
        }
        $this->audience[] = $audience;

        return $this;
    }

    /**
     * @return string[]|null
     */
    public function getAllAudience(): ?array
    {
        return $this->audience;
    }

    
    public function setCount(?int $count): static
    {
        $this->count = null !== $count ? intval($count) : null;

        return $this;
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    public function serialize(DOMNode $parent, SerializationContext $context): void
    {
        $result = $this->createElement('ProxyRestriction', SamlConstants::NS_ASSERTION, $parent, $context);

        $this->attributesToXml(['count'], $result);

        $this->manyElementsToXml($this->getAllAudience(), $result, $context, 'Audience');
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'ProxyRestriction', SamlConstants::NS_ASSERTION);

        $this->attributesFromXml($node, ['count']);

        $this->manyElementsFromXml($node, $context, 'Audience', 'saml', null, 'addAudience');
    }
}
