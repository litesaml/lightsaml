<?php

namespace LightSaml\Model\Assertion;

use DOMNode;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LightSaml\SamlConstants;

class AudienceRestriction extends AbstractCondition
{
    /**
     * @var string[]
     */
    protected $audience = [];

    /**
     * @param string|string[] $audience
     */
    public function __construct(string|array $audience = [])
    {
        if (false == is_array($audience)) {
            $audience = [$audience];
        }
        $this->audience = $audience;
    }

    public function addAudience(string $audience): static
    {
        $this->audience[] = $audience;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getAllAudience(): array
    {
        return $this->audience;
    }

    public function hasAudience(string $value): bool
    {
        if (is_array($this->audience)) {
            foreach ($this->audience as $a) {
                if ($a == $value) {
                    return true;
                }
            }
        }

        return false;
    }

    public function serialize(DOMNode $parent, SerializationContext $context): void
    {
        $result = $this->createElement('AudienceRestriction', SamlConstants::NS_ASSERTION, $parent, $context);

        $this->manyElementsToXml($this->getAllAudience(), $result, $context, 'Audience', SamlConstants::NS_ASSERTION);
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'AudienceRestriction', SamlConstants::NS_ASSERTION);

        $this->audience = [];
        $this->manyElementsFromXml($node, $context, 'Audience', 'saml', null, 'addAudience');
    }
}
