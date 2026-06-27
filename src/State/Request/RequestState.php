<?php

namespace LightSaml\State\Request;

use LightSaml\Meta\ParameterBag;
use Serializable;

class RequestState implements Serializable
{
    private ParameterBag $parameters;

    public function __construct(private ?string $id = null, mixed $nonce = null)
    {
        $this->parameters = new ParameterBag();
        if ($nonce) {
            $this->parameters->set('nonce', $nonce);
        }
    }

    
    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getParameters(): \LightSaml\Meta\ParameterBag
    {
        return $this->parameters;
    }

    /**
     * @deprecated Since 1.2, to be removed in 2.0. Use getParameters() instead
     */
    public function setNonce(mixed $nonce): static
    {
        $this->parameters->set('nonce', $nonce);

        return $this;
    }

    /**
     * @deprecated Since 1.2, to be removed in 2.0. Use getParameters() instead
     */
    public function getNonce(): mixed
    {
        return $this->parameters->get('nonce');
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object.
     *
     * @see http://php.net/manual/en/serializable.serialize.php
     *
     */
    public function serialize(): string
    {
        return serialize($this->__serialize());
    }

    /**
     * (PHP >= 8.1)
     */
    public function __serialize(): array
    {
        $nonce = $this->parameters->get('nonce');

        return [$this->id, $nonce, $this->parameters->__serialize()];
    }

    /**
     * @param string $serialized The string representation of the object
     */
    public function unserialize(string $serialized): void
    {
        $this->__unserialize(unserialize($serialized));
    }

    public function __unserialize(array $serialized): void
    {
        $nonce = null;
        $this->parameters = new ParameterBag();
        [$this->id, $nonce, $parameters] = $serialized;
        $this->parameters->__unserialize($parameters);
    }
}
