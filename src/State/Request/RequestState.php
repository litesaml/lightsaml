<?php

namespace LightSaml\State\Request;

use LightSaml\Meta\ParameterBag;
use Serializable;

class RequestState implements Serializable
{
    private ParameterBag $parameters;

    /**
     * @param string $id
     */
    public function __construct(private $id = null, mixed $nonce = null)
    {
        $this->parameters = new ParameterBag();
        if ($nonce) {
            $this->parameters->set('nonce', $nonce);
        }
    }

    /**
     * @param string $id
     *
     * @return RequestState
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ParameterBag
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @deprecated Since 1.2, to be removed in 2.0. Use getParameters() instead
     *
     * @return RequestState
     */
    public function setNonce(mixed $nonce)
    {
        $this->parameters->set('nonce', $nonce);

        return $this;
    }

    /**
     * @deprecated Since 1.2, to be removed in 2.0. Use getParameters() instead
     *
     * @return mixed
     */
    public function getNonce()
    {
        return $this->parameters->get('nonce');
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object.
     *
     * @see http://php.net/manual/en/serializable.serialize.php
     *
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize($this->__serialize());
    }

    /**
     * (PHP >= 8.1)
     *
     * @return array
     */
    public function __serialize()
    {
        $nonce = $this->parameters->get('nonce');

        return [$this->id, $nonce, $this->parameters->__serialize()];
    }

    /**
     * @param string $serialized The string representation of the object
     *
     * @return void
     */
    public function unserialize($serialized)
    {
        $this->__unserialize(unserialize($serialized));
    }

    /**
     * @return void
     */
    public function __unserialize(array $serialized)
    {
        $nonce = null;
        $this->parameters = new ParameterBag();
        [$this->id, $nonce, $parameters] = $serialized;
        $this->parameters->__unserialize($parameters);
    }
}
