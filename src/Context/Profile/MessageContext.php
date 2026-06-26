<?php

namespace LightSaml\Context\Profile;

use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LightSaml\Model\Protocol\AuthnRequest;
use LightSaml\Model\Protocol\LogoutRequest;
use LightSaml\Model\Protocol\LogoutResponse;
use LightSaml\Model\Protocol\Response;
use LightSaml\Model\Protocol\SamlMessage;

class MessageContext extends AbstractProfileContext
{
    private ?\LightSaml\Model\Protocol\SamlMessage $message = null;

    private ?string $bindingType = null;

    public function getBindingType(): string
    {
        return $this->bindingType;
    }

    public function setBindingType(string $bindingType): static
    {
        $this->bindingType = $bindingType;

        return $this;
    }

    public function getMessage(): ?\LightSaml\Model\Protocol\SamlMessage
    {
        return $this->message;
    }

    public function setMessage(?SamlMessage $message = null): static
    {
        $this->message = $message;

        return $this;
    }

    public function asAuthnRequest(): ?\LightSaml\Model\Protocol\AuthnRequest
    {
        if ($this->message instanceof AuthnRequest) {
            return $this->message;
        }

        return null;
    }

    public function asLogoutRequest(): ?\LightSaml\Model\Protocol\LogoutRequest
    {
        if ($this->message instanceof LogoutRequest) {
            return $this->message;
        }

        return null;
    }

    public function asResponse(): ?\LightSaml\Model\Protocol\Response
    {
        if ($this->message instanceof Response) {
            return $this->message;
        }

        return null;
    }

    public function asLogoutResponse(): ?\LightSaml\Model\Protocol\LogoutResponse
    {
        if ($this->message instanceof LogoutResponse) {
            return $this->message;
        }

        return null;
    }

    public function getSerializationContext(): \LightSaml\Model\Context\SerializationContext
    {
        return $this->getSubContext(ProfileContexts::SERIALIZATION, SerializationContext::class);
    }

    public function getDeserializationContext(): \LightSaml\Model\Context\DeserializationContext
    {
        return $this->getSubContext(ProfileContexts::DESERIALIZATION, DeserializationContext::class);
    }
}
