<?php

namespace LightSaml\State\Sso;

use LightSaml\Meta\ParameterBag;

class SsoState
{
    private ?string $localSessionId = null;

    private ParameterBag $parameters;

    /** @var SsoSessionState[] */
    private array $ssoSessions = [];

    public function __construct()
    {
        $this->parameters = new ParameterBag();
    }

    public function getLocalSessionId(): string
    {
        return $this->localSessionId;
    }

    public function setLocalSessionId(string $localSessionId): static
    {
        $this->localSessionId = $localSessionId;

        return $this;
    }

    public function getParameters(): ParameterBag
    {
        return $this->parameters;
    }

    /**
     * @deprecated Since 1.2, to be removed in 2.0. Use getParameters() instead
     */
    public function getOptions(): array
    {
        return $this->parameters->all();
    }

    /**
     * @deprecated Since 1.2, to be removed in 2.0. Use getParameters() instead
     */
    public function addOption(string $name, mixed $value): static
    {
        $this->parameters->set($name, $value);

        return $this;
    }

    /**
     * @deprecated Since 1.2, to be removed in 2.0. Use getParameters() instead
     */
    public function removeOption(string $name): static
    {
        $this->parameters->remove($name);

        return $this;
    }

    /**
     * @deprecated Since 1.2, to be removed in 2.0. Use getParameters() instead
     */
    public function hasOption(string $name): bool
    {
        return $this->parameters->has($name);
    }

    /**
     * @return SsoSessionState[]
     */
    public function getSsoSessions(): array
    {
        return $this->ssoSessions;
    }

    /**
     * @param SsoSessionState[] $ssoSessions
     */
    public function setSsoSessions(array $ssoSessions): static
    {
        $this->ssoSessions = [];
        foreach ($ssoSessions as $ssoSession) {
            $this->addSsoSession($ssoSession);
        }

        return $this;
    }

    public function addSsoSession(SsoSessionState $ssoSessionState): static
    {
        $this->ssoSessions[] = $ssoSessionState;

        return $this;
    }

    /**
     *
     * @return SsoSessionState[]
     */
    public function filter($idpEntityId, $spEntityId, $nameId, $nameIdFormat, $sessionIndex): array
    {
        $result = [];

        foreach ($this->ssoSessions as $ssoSession) {
            if (
                (!$idpEntityId || $ssoSession->getIdpEntityId() === $idpEntityId)
                && (!$spEntityId || $ssoSession->getSpEntityId() === $spEntityId)
                && (!$nameId || $ssoSession->getNameId() === $nameId)
                && (!$nameIdFormat || $ssoSession->getNameIdFormat() === $nameIdFormat)
                && (!$sessionIndex || $ssoSession->getSessionIndex() === $sessionIndex)
            ) {
                $result[] = $ssoSession;
            }
        }

        return $result;
    }

    public function modify(callable $callback): static
    {
        $this->ssoSessions = array_values(array_filter($this->ssoSessions, $callback));

        return $this;
    }

    public function __serialize(): array
    {
        return [
            $this->localSessionId,
            $this->ssoSessions,
            [],
            $this->parameters,
        ];
    }

    public function __unserialize(array $data): void
    {
        // add a few extra elements in the array to ensure that we have enough keys when unserializing
        // older data which does not include all properties.
        $data = array_merge($data, array_fill(0, 5, null));
        $oldOptions = null;

        [
            $this->localSessionId,
            $this->ssoSessions,
            $oldOptions,
            // old deprecated options
            $this->parameters,
        ] = $data;

        // in case it was serialized in old way, copy old options to parameters
        if ($oldOptions && 0 == $this->parameters->count()) {
            $this->parameters->add($oldOptions);
        }
    }
}
