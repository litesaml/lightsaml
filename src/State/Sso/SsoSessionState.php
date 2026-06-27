<?php

namespace LightSaml\State\Sso;

use DateTime;
use LightSaml\Error\LightSamlException;
use LightSaml\Meta\ParameterBag;

class SsoSessionState
{
    protected ?string $idpEntityId = null;

    protected string $spEntityId = '';

    protected string $nameId = '';

    protected string $nameIdFormat = '';

    protected string $sessionIndex = '';

    protected ?DateTime $sessionInstant = null;

    protected ?DateTime $firstAuthOn = null;

    protected ?DateTime $lastAuthOn = null;

    protected ParameterBag $parameters;

    public function __construct()
    {
        $this->parameters = new ParameterBag();
    }

    public function getIdpEntityId(): ?string
    {
        return $this->idpEntityId;
    }

    public function setIdpEntityId(string $idpEntityId): static
    {
        $this->idpEntityId = $idpEntityId;

        return $this;
    }

    public function getSpEntityId(): string
    {
        return $this->spEntityId;
    }

    public function setSpEntityId(string $spEntityId): static
    {
        $this->spEntityId = $spEntityId;

        return $this;
    }

    public function getNameId(): string
    {
        return $this->nameId;
    }

    public function setNameId(string $nameId): static
    {
        $this->nameId = $nameId;

        return $this;
    }

    public function getNameIdFormat(): string
    {
        return $this->nameIdFormat;
    }

    public function setNameIdFormat(string $nameIdFormat): static
    {
        $this->nameIdFormat = $nameIdFormat;

        return $this;
    }

    public function getSessionIndex(): string
    {
        return $this->sessionIndex;
    }

    public function setSessionIndex(string $sessionIndex): static
    {
        $this->sessionIndex = $sessionIndex;

        return $this;
    }

    public function getFirstAuthOn(): ?DateTime
    {
        return $this->firstAuthOn;
    }

    public function setFirstAuthOn(DateTime $firstAuthOn): static
    {
        $this->firstAuthOn = $firstAuthOn;

        return $this;
    }

    public function getLastAuthOn(): ?DateTime
    {
        return $this->lastAuthOn;
    }

    public function setLastAuthOn(DateTime $lastAuthOn): static
    {
        $this->lastAuthOn = $lastAuthOn;

        return $this;
    }

    public function getSessionInstant(): ?DateTime
    {
        return $this->sessionInstant;
    }

    public function setSessionInstant(DateTime $sessionInstant): static
    {
        $this->sessionInstant = $sessionInstant;

        return $this;
    }

    public function getParameters(): ParameterBag
    {
        return $this->parameters;
    }

    /**
     * @deprecated Since 1.2, will be removed in 2.0. Use getParameters() instead
     */
    public function getOptions(): array
    {
        return $this->parameters->all();
    }

    /**
     * @deprecated Since 1.2, will be removed in 2.0. Use getParameters() instead
     */
    public function addOption(string $name, mixed $value): static
    {
        $this->parameters->set($name, $value);

        return $this;
    }

    /**
     * @deprecated Since 1.2, will be removed in 2.0. Use getParameters() instead
     */
    public function removeOption(string $name): static
    {
        $this->parameters->remove($name);

        return $this;
    }

    /**
     * @deprecated Since 1.2, will be removed in 2.0. Use getParameters() instead
     */
    public function hasOption(string $name): bool
    {
        return $this->parameters->has($name);
    }

    /**
     *
     * @return string Other party id
     *
     * @throws LightSamlException If $partyId does not match sp or idp entity id
     */
    public function getOtherPartyId(string $partyId): string
    {
        if ($partyId == $this->idpEntityId) {
            return $this->spEntityId;
        } elseif ($partyId == $this->spEntityId) {
            return $this->idpEntityId;
        }

        throw new LightSamlException(sprintf('Party "%s" is not included in sso session between "%s" and "%s"', $partyId, $this->idpEntityId, $this->spEntityId));
    }

    public function __serialize(): array
    {
        return [
            $this->idpEntityId,
            $this->spEntityId,
            $this->nameId,
            $this->nameIdFormat,
            $this->sessionIndex,
            isset($this->sessionInstant) ? $this->sessionInstant : null,
            isset($this->firstAuthOn) ? $this->firstAuthOn : null,
            isset($this->lastAuthOn) ? $this->lastAuthOn : null,
            [],
            $this->parameters,
        ];
    }

    public function __unserialize(array $data): void
    {
        // add a few extra elements in the array to ensure that we have enough keys when unserializing
        // older data which does not include all properties.
        $data = array_merge($data, array_fill(0, 5, null));

        [$this->idpEntityId, $this->spEntityId, $this->nameId, $this->nameIdFormat, $this->sessionIndex, $this->sessionInstant, $this->firstAuthOn, $this->lastAuthOn, $options, $this->parameters] = $data;

        // if deserialized from old format, set old options to new parameters
        if ($options && 0 == $this->parameters->count()) {
            $this->parameters->replace($options);
        }
    }
}
