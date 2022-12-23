<?php

namespace LightSaml\Meta;

class ParameterBag implements \IteratorAggregate, \Countable, \Serializable
{
    /**
     * Parameter storage.
     *
     * @var array
     */
    protected $parameters;

    /**
     * @param array $parameters An array of parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    /**
     * Returns the parameters.
     *
     * @return array An array of parameters
     */
    public function all()
    {
        return $this->parameters;
    }

    /**
     * Returns the parameter keys.
     *
     * @return array An array of parameter keys
     */
    public function keys()
    {
        return array_keys($this->parameters);
    }

    /**
     * Replaces the current parameters by a new set.
     *
     * @param array $parameters An array of parameters
     */
    public function replace(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    /**
     * Adds parameters.
     */
    public function add(array $parameters = [])
    {
        $this->parameters = array_replace($this->parameters, $parameters);
    }

    /**
     * Returns a parameter by name.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return array_key_exists($key, $this->parameters) ? $this->parameters[$key] : $default;
    }

    /**
     * Sets a parameter by name.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value)
    {
        $this->parameters[$key] = $value;
    }

    /**
     * Returns true if the parameter is defined.
     *
     * @param string $key
     *
     * @return bool true if the parameter exists, false otherwise
     */
    public function has($key)
    {
        return array_key_exists($key, $this->parameters);
    }

    /**
     * Removes a parameter.
     *
     * @param string $key
     */
    public function remove($key)
    {
        unset($this->parameters[$key]);
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->parameters);
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
    public function unserialize($serialized)
    {
        $this->__unserialize(unserialize($serialized));
    }

    public function __unserialize(array $serialized)
    {
        $this->parameters = $serialized;
    }
}
