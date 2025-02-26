<?php

namespace LightSaml\State\Sso;

use LightSaml\Meta\ParameterBag;
use Serializable;

class SsoState implements Serializable
{
    /** @var string */
    private $localSessionId;

    private ParameterBag $parameters;

    /** @var SsoSessionState[] */
    private $ssoSessions = [];

    public function __construct()
    {
        $this->parameters = new ParameterBag();
    }

    /**
     * @return string
     */
    public function getLocalSessionId()
    {
        return $this->localSessionId;
    }

    /**
     * @param string $localSessionId
     *
     * @return SsoState
     */
    public function setLocalSessionId($localSessionId)
    {
        $this->localSessionId = $localSessionId;

        return $this;
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
     * @return array
     */
    public function getOptions()
    {
        return $this->parameters->all();
    }

    /**
     * @deprecated Since 1.2, to be removed in 2.0. Use getParameters() instead
     *
     * @param string $name
     *
     * @return SsoState
     */
    public function addOption($name, mixed $value)
    {
        $this->parameters->set($name, $value);

        return $this;
    }

    /**
     * @deprecated Since 1.2, to be removed in 2.0. Use getParameters() instead
     *
     * @param string $name
     *
     * @return SsoState
     */
    public function removeOption($name)
    {
        $this->parameters->remove($name);

        return $this;
    }

    /**
     * @deprecated Since 1.2, to be removed in 2.0. Use getParameters() instead
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasOption($name)
    {
        return $this->parameters->has($name);
    }

    /**
     * @return SsoSessionState[]
     */
    public function getSsoSessions()
    {
        return $this->ssoSessions;
    }

    /**
     * @param SsoSessionState[] $ssoSessions
     *
     * @return SsoState
     */
    public function setSsoSessions(array $ssoSessions)
    {
        $this->ssoSessions = [];
        foreach ($ssoSessions as $ssoSession) {
            $this->addSsoSession($ssoSession);
        }

        return $this;
    }

    /**
     * @return SsoState
     */
    public function addSsoSession(SsoSessionState $ssoSessionState)
    {
        $this->ssoSessions[] = $ssoSessionState;

        return $this;
    }

    /**
     *
     * @return SsoSessionState[]
     */
    public function filter($idpEntityId, $spEntityId, $nameId, $nameIdFormat, $sessionIndex)
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

    /**
     * @param callable $callback
     *
     * @return SsoState
     */
    public function modify($callback)
    {
        $this->ssoSessions = array_values(array_filter($this->ssoSessions, $callback));

        return $this;
    }

    /**
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
        return [
            $this->localSessionId,
            $this->ssoSessions,
            [],
            $this->parameters,
        ];
    }

    /**
     * @param string $serialized
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
    public function __unserialize(array $data)
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
