<?php

namespace LightSaml\Meta;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/** @implements IteratorAggregate<string, mixed> */
class ParameterBag implements IteratorAggregate, Countable
{
    /** @param array<string, mixed> $parameters */
    public function __construct(protected array $parameters = [])
    {
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return $this->parameters;
    }

    /** @return array<int, string> */
    public function keys(): array
    {
        return array_keys($this->parameters);
    }

    /** @param array<string, mixed> $parameters */
    public function replace(array $parameters = []): void
    {
        $this->parameters = $parameters;
    }

    /** @param array<string, mixed> $parameters */
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

    /** @return ArrayIterator<string, mixed> */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->parameters);
    }

    public function count(): int
    {
        return count($this->parameters);
    }

    /** @return array<string, mixed> */
    public function __serialize(): array
    {
        return $this->parameters;
    }

    /** @param array<string, mixed> $serialized */
    public function __unserialize(array $serialized): void
    {
        $this->parameters = $serialized;
    }
}
