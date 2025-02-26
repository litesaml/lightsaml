<?php

namespace LightSaml\Resolver\Credential;

use InvalidArgumentException;
use LightSaml\Credential\CredentialInterface;
use LightSaml\Criteria\CriteriaSet;

class CredentialResolverQuery extends CriteriaSet
{
    /** @var CredentialInterface[] */
    private $arrCredentials;

    public function __construct(private readonly CredentialResolverInterface $resolver)
    {
    }

    /**
     * @return CredentialResolverQuery
     */
    public function resolve()
    {
        $this->arrCredentials = $this->resolver->resolve($this);

        return $this;
    }

    /**
     * @return CredentialInterface|null
     */
    public function firstCredential()
    {
        return reset($this->arrCredentials) ?: null;
    }

    /**
     * @return CredentialInterface[]
     */
    public function allCredentials()
    {
        return $this->arrCredentials;
    }

    /**
     * @return CredentialInterface[]
     */
    public function getPublicKeys()
    {
        $result = [];
        foreach ($this->arrCredentials as $credential) {
            if ($credential instanceof CredentialInterface) {
                $publicKey = $credential->getPublicKey();
                if ($publicKey) {
                    $result[] = $credential;
                }
            } else {
                throw new InvalidArgumentException('Expected CredentialInterface');
            }
        }

        return $result;
    }

    /**
     * @return CredentialInterface[]
     */
    public function getPrivateKeys()
    {
        $result = [];
        foreach ($this->arrCredentials as $credential) {
            if ($credential instanceof CredentialInterface) {
                $privateKey = $credential->getPrivateKey();
                if ($privateKey) {
                    $result[] = $credential;
                }
            } else {
                throw new InvalidArgumentException('Expected CredentialInterface');
            }
        }

        return $result;
    }
}
