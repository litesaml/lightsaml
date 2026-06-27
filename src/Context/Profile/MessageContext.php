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
    private ?SamlMessage $message = null;

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

    public function getMessage(): ?SamlMessage
    {
        return $this->message;
    }

    public function setMessage(?SamlMessage $message = null): static
    {
        $this->message = $message;

        return $this;
    }

    public function asAuthnRequest(): ?AuthnRequest
    {
        if ($this->message instanceof AuthnRequest) {
            return $this->message;
        }

        return null;
    }

    public function asLogoutRequest(): ?LogoutRequest
    {
        if ($this->message instanceof LogoutRequest) {
            return $this->message;
        }

        return null;
    }

    public function asResponse(): ?Response
    {
        if ($this->message instanceof Response) {
            return $this->message;
        }

        return null;
    }

    public function asLogoutResponse(): ?LogoutResponse
    {
        if ($this->message instanceof LogoutResponse) {
            return $this->message;
        }

        return null;
    }

    public function getSerializationContext(): SerializationContext
    {
        return $this->getSubContext(ProfileContexts::SERIALIZATION, SerializationContext::class);
    }

    public function getDeserializationContext(): DeserializationContext
    {
        return $this->getSubContext(ProfileContexts::DESERIALIZATION, DeserializationContext::class);
    }
}
