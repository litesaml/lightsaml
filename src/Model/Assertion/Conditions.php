<?php

namespace LightSaml\Model\Assertion;

use DateTime;
use DOMNode;
use LightSaml\Helper;
use LightSaml\Model\AbstractSamlModel;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LightSaml\SamlConstants;

class Conditions extends AbstractSamlModel
{
    /**
     * @var int|null
     */
    protected $notBefore;

    /**
     * @var int|null
     */
    protected $notOnOrAfter;

    /**
     * @var array|AbstractCondition[]|AudienceRestriction[]|OneTimeUse[]|ProxyRestriction[]
     */
    protected $items = [];

    public function addItem(AbstractCondition $item): static
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * @return AbstractCondition[]|AudienceRestriction[]|OneTimeUse[]|ProxyRestriction[]|array
     */
    public function getAllItems(): array
    {
        return $this->items;
    }

    /**
     * @return AudienceRestriction[]
     */
    public function getAllAudienceRestrictions(): array
    {
        $result = [];
        foreach ($this->items as $item) {
            if ($item instanceof AudienceRestriction) {
                $result[] = $item;
            }
        }

        return $result;
    }

    public function getFirstAudienceRestriction(): ?\LightSaml\Model\Assertion\AudienceRestriction
    {
        foreach ($this->items as $item) {
            if ($item instanceof AudienceRestriction) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @return OneTimeUse[]
     */
    public function getAllOneTimeUses(): array
    {
        $result = [];
        foreach ($this->items as $item) {
            if ($item instanceof OneTimeUse) {
                $result[] = $item;
            }
        }

        return $result;
    }

    public function getFirstOneTimeUse(): ?\LightSaml\Model\Assertion\OneTimeUse
    {
        foreach ($this->items as $item) {
            if ($item instanceof OneTimeUse) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @return ProxyRestriction[]
     */
    public function getAllProxyRestrictions(): array
    {
        $result = [];
        foreach ($this->items as $item) {
            if ($item instanceof ProxyRestriction) {
                $result[] = $item;
            }
        }

        return $result;
    }

    public function getFirstProxyRestriction(): ?\LightSaml\Model\Assertion\ProxyRestriction
    {
        foreach ($this->items as $item) {
            if ($item instanceof ProxyRestriction) {
                return $item;
            }
        }

        return null;
    }

    public function setNotBefore(int|string|\DateTime $notBefore): static
    {
        $this->notBefore = Helper::getTimestampFromValue($notBefore);

        return $this;
    }

    public function getNotBeforeTimestamp(): ?int
    {
        return $this->notBefore;
    }

    public function getNotBeforeString(): ?string
    {
        if ($this->notBefore) {
            return Helper::time2string($this->notBefore);
        }

        return null;
    }

    public function getNotBeforeDateTime(): ?\DateTime
    {
        if ($this->notBefore) {
            return new DateTime('@' . $this->notBefore);
        }

        return null;
    }

    public function setNotOnOrAfter(int|string|\DateTime $notOnOrAfter): static
    {
        $this->notOnOrAfter = Helper::getTimestampFromValue($notOnOrAfter);

        return $this;
    }

    public function getNotOnOrAfterTimestamp(): ?int
    {
        return $this->notOnOrAfter;
    }

    public function getNotOnOrAfterString(): ?string
    {
        if ($this->notOnOrAfter) {
            return Helper::time2string($this->notOnOrAfter);
        }

        return null;
    }

    public function getNotOnOrAfterDateTime(): ?\DateTime
    {
        if ($this->notOnOrAfter) {
            return new DateTime('@' . $this->notOnOrAfter);
        }

        return null;
    }

    public function serialize(DOMNode $parent, SerializationContext $context): void
    {
        $result = $this->createElement('Conditions', SamlConstants::NS_ASSERTION, $parent, $context);

        $this->attributesToXml(
            ['NotBefore', 'NotOnOrAfter'],
            $result
        );

        foreach ($this->items as $item) {
            $item->serialize($result, $context);
        }
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'Conditions', SamlConstants::NS_ASSERTION);

        $this->attributesFromXml($node, ['NotBefore', 'NotOnOrAfter']);

        $this->manyElementsFromXml(
            $node,
            $context,
            'AudienceRestriction',
            'saml',
            AudienceRestriction::class,
            'addItem'
        );
        $this->manyElementsFromXml(
            $node,
            $context,
            'OneTimeUse',
            'saml',
            OneTimeUse::class,
            'addItem'
        );
        $this->manyElementsFromXml(
            $node,
            $context,
            'ProxyRestriction',
            'saml',
            ProxyRestriction::class,
            'addItem'
        );
    }
}
