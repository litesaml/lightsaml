<?php

namespace LightSaml\Model\Metadata;

use DOMNode;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;

class IndexedEndpoint extends Endpoint
{
    /** @var int */
    protected $index;

    /** @var bool|null */
    protected $isDefault;

    /**
     * @param bool|null $isDefault
     *
     * @return IndexedEndpoint
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = filter_var($isDefault, FILTER_VALIDATE_BOOLEAN, ['flags' => FILTER_NULL_ON_FAILURE]);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getIsDefaultString()
    {
        return $this->isDefault ? 'true' : 'false';
    }

    /**
     * @return bool|null
     */
    public function getIsDefaultBool()
    {
        return $this->isDefault;
    }

    /**
     * @param int $index
     *
     * @return IndexedEndpoint
     */
    public function setIndex($index)
    {
        $this->index = (int) $index;

        return $this;
    }

    /**
     * @return int
     */
    public function getIndex()
    {
        return $this->index;
    }

    public function serialize(DOMNode $parent, SerializationContext $context)
    {
        $this->attributesToXml(['index', 'isDefault'], $parent);
        parent::serialize($parent, $context);
    }

    public function deserialize(DOMNode $node, DeserializationContext $context)
    {
        $this->attributesFromXml($node, ['index', 'isDefault']);

        parent::deserialize($node, $context);
    }
}
