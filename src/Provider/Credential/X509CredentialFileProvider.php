<?php

namespace LightSaml\Provider\Credential;

use LightSaml\Credential\KeyHelper;
use LightSaml\Credential\X509Certificate;
use LightSaml\Credential\X509Credential;

class X509CredentialFileProvider implements CredentialProviderInterface
{
    /** @var X509Credential */
    private $credential;

    /**
     * @param string $entityId
     * @param string $certificatePath
     * @param string $privateKeyPath
     * @param string $privateKeyPassword
     */
    public function __construct(private $entityId, private $certificatePath, private $privateKeyPath, private $privateKeyPassword)
    {
    }

    /**
     * @return X509Credential
     */
    public function get()
    {
        if (null == $this->credential) {
            $this->credential = new X509Credential(
                X509Certificate::fromFile($this->certificatePath),
                KeyHelper::createPrivateKey($this->privateKeyPath, $this->privateKeyPassword, true)
            );
            $this->credential->setEntityId($this->entityId);
        }

        return $this->credential;
    }
}
