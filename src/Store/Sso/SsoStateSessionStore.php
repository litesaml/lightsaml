<?php

namespace LightSaml\Store\Sso;

use LightSaml\Error\LightSamlSessionNotFoundException;
use LightSaml\State\Sso\SsoState;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SsoStateSessionStore implements SsoStateStoreInterface
{
    /** @var null|SessionInterface */
    protected $session;

    /** @var string */
    protected $key;

    /**
     * @param string $key
     */
    public function __construct(?SessionInterface $session, $key)
    {
        $this->session = $session;
        $this->key = $key;
    }

    /**
     * @return SsoState
     */
    public function get()
    {
        $result = $this->getSession()->get($this->key);
        if (null == $result) {
            $result = new SsoState();
            $this->set($result);
        }

        return $result;
    }

    /**
     * @return void
     */
    public function set(SsoState $ssoState)
    {
        $ssoState->setLocalSessionId($this->getSession()->getId());
        $this->getSession()->set($this->key, $ssoState);
    }

    protected function getSession(): SessionInterface
    {
        if (null !== $this->session) {
            return $this->session;
        }

        throw new LightSamlSessionNotFoundException('Session Not Found');
    }
}
