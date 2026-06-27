<?php

namespace LightSaml\Model\Metadata;

use DateTime;
use DOMNode;
use InvalidArgumentException;
use LightSaml\Context\Model\DeserializationContext;
use LightSaml\Context\Model\SerializationContext;
use LightSaml\Helper;
use LightSaml\Model\XmlDSig\Signature;
use LightSaml\Model\XmlDSig\SignatureXmlReader;
use LightSaml\SamlConstants;

class EntitiesDescriptor extends Metadata
{
    protected ?int $validUntil = null;

    protected ?string $cacheDuration = null;

    protected ?string $id = null;

    protected ?string $name = null;

    protected ?Signature $signature = null;

    /** @var EntitiesDescriptor[]|EntityDescriptor[] */
    protected array $items = [];

    public static function load(string $filename): EntitiesDescriptor
    {
        return self::loadXml(file_get_contents($filename));
    }

    public static function loadXml(string $xml): self
    {
        $context = new DeserializationContext();
        $context->getDocument()->loadXML($xml);
        $ed = new self();
        $ed->deserialize($context->getDocument(), $context);

        return $ed;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setCacheDuration(?string $cacheDuration): static
    {
        if ($cacheDuration !== null) {
            Helper::validateDurationString($cacheDuration);
        }

        $this->cacheDuration = $cacheDuration;

        return $this;
    }

    public function getCacheDuration(): ?string
    {
        return $this->cacheDuration;
    }

    public function setID(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getID(): ?string
    {
        return $this->id;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setSignature(Signature $signature): static
    {
        $this->signature = $signature;

        return $this;
    }

    public function getSignature(): ?Signature
    {
        return $this->signature;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setValidUntil(int|string|DateTime $validUntil): static
    {
        $value = Helper::getTimestampFromValue($validUntil);
        if ($value < 0) {
            throw new InvalidArgumentException('Invalid validUntil');
        }
        $this->validUntil = $value;

        return $this;
    }

    public function getValidUntilString(): ?string
    {
        if ($this->validUntil) {
            return Helper::time2string($this->validUntil);
        }

        return null;
    }

    public function getValidUntilTimestamp(): int
    {
        return $this->validUntil;
    }

    public function getValidUntilDateTime(): ?DateTime
    {
        if ($this->validUntil) {
            return new DateTime('@' . $this->validUntil);
        }

        return null;
    }

    /**
     *
     * @throws InvalidArgumentException
     */
    public function addItem(mixed $item): static
    {
        if (false == $item instanceof self && false == $item instanceof EntityDescriptor) {
            throw new InvalidArgumentException('Expected EntitiesDescriptor or EntityDescriptor');
        }
        if ($item === $this) {
            throw new InvalidArgumentException('Circular reference detected');
        }
        if ($item instanceof self && $item->containsItem($this)) {
            throw new InvalidArgumentException('Circular reference detected');
        }
        $this->items[] = $item;

        return $this;
    }

    /**
     *
     * @throws InvalidArgumentException
     */
    public function containsItem(mixed $item): bool
    {
        if (false == $item instanceof self && false == $item instanceof EntityDescriptor) {
            throw new InvalidArgumentException('Expected EntitiesDescriptor or EntityDescriptor');
        }
        foreach ($this->items as $i) {
            if ($i === $item) {
                return true;
            }
            if ($i instanceof self && $i->containsItem($item)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return EntitiesDescriptor[]|EntityDescriptor[]
     */
    public function getAllItems(): array
    {
        return $this->items;
    }

    /**
     * @return EntityDescriptor[]
     */
    public function getAllEntityDescriptors(): array
    {
        $result = [];
        foreach ($this->items as $item) {
            if ($item instanceof self) {
                $result = array_merge($result, $item->getAllEntityDescriptors());
            } else {
                $result[] = $item;
            }
        }

        return $result;
    }

    public function getByEntityId(string $entityId): ?EntityDescriptor
    {
        foreach ($this->getAllEntityDescriptors() as $entityDescriptor) {
            if ($entityDescriptor->getEntityID() == $entityId) {
                return $entityDescriptor;
            }
        }

        return null;
    }

    public function serialize(DOMNode $parent, SerializationContext $context): void
    {
        $result = $this->createElement('EntitiesDescriptor', SamlConstants::NS_METADATA, $parent, $context);

        $this->attributesToXml(['validUntil', 'cacheDuration', 'ID', 'Name'], $result);

        $this->singleElementsToXml(['Signature'], $result, $context);

        $this->manyElementsToXml($this->getAllItems(), $result, $context);
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'EntitiesDescriptor', SamlConstants::NS_METADATA);

        $this->attributesFromXml($node, ['validUntil', 'cacheDuration', 'ID', 'Name']);

        $this->singleElementsFromXml($node, $context, [
            'Signature' => ['ds', SignatureXmlReader::class],
        ]);

        $this->manyElementsFromXml(
            $node,
            $context,
            'EntityDescriptor',
            'md',
            EntityDescriptor::class,
            'addItem'
        );
        $this->manyElementsFromXml(
            $node,
            $context,
            'EntitiesDescriptor',
            'md',
            EntitiesDescriptor::class,
            'addItem'
        );
    }
}
