<?php

namespace LightSaml\State\Request;

use LightSaml\Meta\ParameterBag;

class RequestState
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

    public function getParameters(): ParameterBag
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

    public function __serialize(): array
    {
        $nonce = $this->parameters->get('nonce');

        return [$this->id, $nonce, $this->parameters->__serialize()];
    }

    /** @param array<mixed> $serialized */
    public function __unserialize(array $serialized): void
    {
        $nonce = null;
        $this->parameters = new ParameterBag();
        [$this->id, $nonce, $parameters] = $serialized;
        $this->parameters->__unserialize($parameters);
    }
}
