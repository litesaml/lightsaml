<?php

namespace LightSaml\Provider\Credential;

use LightSaml\Credential\KeyHelper;
use LightSaml\Credential\X509Certificate;
use LightSaml\Credential\X509Credential;

class X509CredentialFileProvider implements CredentialProviderInterface
{
    private ?X509Credential $credential = null;

    public function __construct(private readonly string $entityId, private readonly string $certificatePath, private readonly string $privateKeyPath, private readonly ?string $privateKeyPassword)
    {
    }

    public function get(): X509Credential
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
