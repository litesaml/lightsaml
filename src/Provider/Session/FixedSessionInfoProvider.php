<?php

namespace LightSaml\Provider\Session;

class FixedSessionInfoProvider implements SessionInfoProviderInterface
{
    /**
     * @param int    $authnInstant
     * @param string $sessionIndex
     * @param string $authnContextClassRef
     */
    public function __construct(protected $authnInstant = 0, protected $sessionIndex = null, protected $authnContextClassRef = null)
    {
    }

    public function setAuthnInstant(int $authnInstant): static
    {
        $this->authnInstant = intval($authnInstant);

        return $this;
    }

    public function setSessionIndex(string $sessionIndex): static
    {
        $this->sessionIndex = $sessionIndex;

        return $this;
    }

    public function setAuthnContextClassRef(string $authnContextClassRef): static
    {
        $this->authnContextClassRef = $authnContextClassRef;

        return $this;
    }

    public function getAuthnInstant(): int
    {
        return $this->authnInstant;
    }

    public function getSessionIndex(): string
    {
        return $this->sessionIndex;
    }

    public function getAuthnContextClassRef(): string
    {
        return $this->authnContextClassRef;
    }
}
