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

    /**
     * @param int $authnInstant
     *
     * @return FixedSessionInfoProvider
     */
    public function setAuthnInstant($authnInstant)
    {
        $this->authnInstant = intval($authnInstant);

        return $this;
    }

    /**
     * @param string $sessionIndex
     *
     * @return FixedSessionInfoProvider
     */
    public function setSessionIndex($sessionIndex)
    {
        $this->sessionIndex = $sessionIndex;

        return $this;
    }

    /**
     * @param string $authnContextClassRef
     *
     * @return FixedSessionInfoProvider
     */
    public function setAuthnContextClassRef($authnContextClassRef)
    {
        $this->authnContextClassRef = $authnContextClassRef;

        return $this;
    }

    /**
     * @return int
     */
    public function getAuthnInstant()
    {
        return $this->authnInstant;
    }

    /**
     * @return string
     */
    public function getSessionIndex()
    {
        return $this->sessionIndex;
    }

    /**
     * @return string
     */
    public function getAuthnContextClassRef()
    {
        return $this->authnContextClassRef;
    }
}
