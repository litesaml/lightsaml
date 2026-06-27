<?php

namespace LightSaml\Meta;

use ArrayIterator;
use Countable;
use IteratorAggregate;

class ParameterBag implements IteratorAggregate, Countable
{
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

    public function add(array $parameters = []): void
    {
        $this->parameters = array_replace($this->parameters, $parameters);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return array_key_exists($key, $this->parameters) ? $this->parameters[$key] : $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->parameters[$key] = $value;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->parameters);
    }

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

    public function __serialize(): array
    {
        return $this->parameters;
    }

    public function __unserialize(array $serialized): void
    {
        $this->parameters = $serialized;
    }
}
