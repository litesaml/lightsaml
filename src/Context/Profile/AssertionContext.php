<?php

namespace LightSaml\Context\Profile;

use LightSaml\Model\Assertion\Assertion;
use LightSaml\Model\Assertion\EncryptedElement;

class AssertionContext extends AbstractProfileContext
{
    private ?Assertion $assertion = null;

    private ?EncryptedElement $encryptedAssertion = null;

    private ?string $id = null;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getAssertion(): ?Assertion
    {
        return $this->assertion;
    }

    public function setAssertion(?Assertion $assertion = null): static
    {
        $this->assertion = $assertion;

        return $this;
    }

    public function getEncryptedAssertion(): ?EncryptedElement
    {
        return $this->encryptedAssertion;
    }

    public function setEncryptedAssertion(?EncryptedElement $encryptedAssertion = null): static
    {
        $this->encryptedAssertion = $encryptedAssertion;

        return $this;
    }
}
