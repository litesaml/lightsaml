<?php

namespace LightSaml\Resolver\Credential;

use LightSaml\Credential\CredentialInterface;
use LightSaml\Criteria\CriteriaSet;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class CredentialResolverQuery extends CriteriaSet
{
    /** @var CredentialInterface[] */
    private ?array $arrCredentials = null;

    public function __construct(private readonly CredentialResolverInterface $resolver)
    {
    }

    public function resolve(): static
    {
        $this->arrCredentials = $this->resolver->resolve($this);

        return $this;
    }

    public function firstCredential(): ?CredentialInterface
    {
        return reset($this->arrCredentials) ?: null;
    }

    /**
     * @return CredentialInterface[]
     */
    public function allCredentials(): array
    {
        return $this->arrCredentials;
    }

    /**
     * @return XMLSecurityKey[]
     */
    public function getPublicKeys(): array
    {
        $result = [];
        foreach ($this->arrCredentials as $credential) {
            $publicKey = $credential->getPublicKey();
            if ($publicKey instanceof XMLSecurityKey) {
                $result[] = $publicKey;
            }
        }

        return $result;
    }

    /**
     * @return XMLSecurityKey[]
     */
    public function getPrivateKeys(): array
    {
        $result = [];
        foreach ($this->arrCredentials as $credential) {
            $privateKey = $credential->getPrivateKey();
            if ($privateKey instanceof XMLSecurityKey) {
                $result[] = $privateKey;
            }
        }

        return $result;
    }
}
