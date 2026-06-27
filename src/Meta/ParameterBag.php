<?php

namespace LightSaml\Meta;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Serializable;

class ParameterBag implements IteratorAggregate, Countable, Serializable
{
    /**
     * @param array $parameters An array of parameters
     */
    public function __construct(protected array $parameters = [])
    {
    }

    public function all(): array
    {
        return $this->parameters;
    }

    public function keys(): array
    {
        return array_keys($this->parameters);
    }

    public function replace(array $parameters = []): void
    {
        $this->parameters = $parameters;
    }

    /**
     * Adds parameters.
     */
    public function add(array $parameters = []): void
    {
        $this->parameters = array_replace($this->parameters, $parameters);
    }

    /**
     * Returns a parameter by name.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return array_key_exists($key, $this->parameters) ? $this->parameters[$key] : $default;
    }

    /**
     * Sets a parameter by name.
     */
    public function set(string $key, mixed $value): void
    {
        $this->parameters[$key] = $value;
    }

    /**
     * Returns true if the parameter is defined.
     *
     *
     * @return bool true if the parameter exists, false otherwise
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->parameters);
    }

    /**
     * Removes a parameter.
     */
    public function remove(string $key): void
    {
        unset($this->parameters[$key]);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->parameters);
    }

    public function count(): int
    {
        return count($this->parameters);
    }

    /**
     * @deprecated Since php 8.1. Use __serialize() instead
     */
    public function serialize(): string
    {
        return serialize($this->__serialize());
    }

    public function __serialize(): array
    {
        return $this->parameters;
    }

    /**
     * @deprecated Since php 8.1. Use __unserialize() instead
     */
    public function unserialize($serialized): void
    {
        $this->__unserialize(unserialize($serialized));
    }

    public function __unserialize(array $serialized)
    {
        $this->parameters = $serialized;
    }
}
