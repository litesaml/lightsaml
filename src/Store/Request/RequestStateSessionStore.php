<?php

namespace LightSaml\Store\Request;

use LightSaml\Error\LightSamlSessionNotFoundException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class RequestStateSessionStore extends AbstractRequestStateArrayStore
{
    /** @var null|SessionInterface */
    protected $session;

    /** @var string */
    protected $providerId;

    /** @var string */
    protected $prefix;

    /**
     * @param string $providerId
     * @param string $prefix
     */
    public function __construct(?SessionInterface $session, $providerId, $prefix = 'saml_request_state_')
    {
        $this->session = $session;
        $this->providerId = $providerId;
        $this->prefix = $prefix;
    }

    /**
     * @return string
     */
    protected function getKey()
    {
        return sprintf('%s_%s', $this->providerId, $this->prefix);
    }

    protected function getSession(): SessionInterface
    {
        if (null !== $this->session) {
            return $this->session;
        }

        throw new LightSamlSessionNotFoundException('Session Not Found');
    }

    /**
     * @return array
     */
    protected function getArray()
    {
        return $this->getSession()->get($this->getKey(), []);
    }

    protected function setArray(array $arr)
    {
        $this->getSession()->set($this->getKey(), $arr);
    }
}
