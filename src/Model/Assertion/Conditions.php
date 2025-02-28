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

    /**
     * @return Conditions
     */
    public function addItem(AbstractCondition $item)
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * @return AbstractCondition[]|AudienceRestriction[]|OneTimeUse[]|ProxyRestriction[]|array
     */
    public function getAllItems()
    {
        return $this->items;
    }

    /**
     * @return AudienceRestriction[]
     */
    public function getAllAudienceRestrictions()
    {
        $result = [];
        foreach ($this->items as $item) {
            if ($item instanceof AudienceRestriction) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * @return AudienceRestriction|null
     */
    public function getFirstAudienceRestriction()
    {
        foreach ($this->items as $item) {
            if ($item instanceof AudienceRestriction) {
                return $item;
            }
        }

        return;
    }

    /**
     * @return OneTimeUse[]
     */
    public function getAllOneTimeUses()
    {
        $result = [];
        foreach ($this->items as $item) {
            if ($item instanceof OneTimeUse) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * @return OneTimeUse|null
     */
    public function getFirstOneTimeUse()
    {
        foreach ($this->items as $item) {
            if ($item instanceof OneTimeUse) {
                return $item;
            }
        }

        return;
    }

    /**
     * @return ProxyRestriction[]
     */
    public function getAllProxyRestrictions()
    {
        $result = [];
        foreach ($this->items as $item) {
            if ($item instanceof ProxyRestriction) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * @return ProxyRestriction|null
     */
    public function getFirstProxyRestriction()
    {
        foreach ($this->items as $item) {
            if ($item instanceof ProxyRestriction) {
                return $item;
            }
        }

        return;
    }

    /**
     * @param int|string|DateTime $notBefore
     *
     * @return Conditions
     */
    public function setNotBefore($notBefore)
    {
        $this->notBefore = Helper::getTimestampFromValue($notBefore);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getNotBeforeTimestamp()
    {
        return $this->notBefore;
    }

    /**
     * @return string|null
     */
    public function getNotBeforeString()
    {
        if ($this->notBefore) {
            return Helper::time2string($this->notBefore);
        }

        return;
    }

    /**
     * @return DateTime|null
     */
    public function getNotBeforeDateTime()
    {
        if ($this->notBefore) {
            return new DateTime('@' . $this->notBefore);
        }

        return;
    }

    /**
     * @param int|string|DateTime $notOnOrAfter
     *
     * @return Conditions
     */
    public function setNotOnOrAfter($notOnOrAfter)
    {
        $this->notOnOrAfter = Helper::getTimestampFromValue($notOnOrAfter);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getNotOnOrAfterTimestamp()
    {
        return $this->notOnOrAfter;
    }

    /**
     * @return string|null
     */
    public function getNotOnOrAfterString()
    {
        if ($this->notOnOrAfter) {
            return Helper::time2string($this->notOnOrAfter);
        }

        return;
    }

    /**
     * @return DateTime|null
     */
    public function getNotOnOrAfterDateTime()
    {
        if ($this->notOnOrAfter) {
            return new DateTime('@' . $this->notOnOrAfter);
        }

        return;
    }

    /**
     * @return void
     */
    public function serialize(DOMNode $parent, SerializationContext $context)
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

    public function deserialize(DOMNode $node, DeserializationContext $context)
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
