<?php

namespace LightSaml\Store\Credential;

use LightSaml\Credential\CredentialInterface;
use LightSaml\Credential\KeyHelper;
use LightSaml\Credential\X509Certificate;
use LightSaml\Credential\X509Credential;

class X509FileCredentialStore implements CredentialStoreInterface
{
    private ?\LightSaml\Credential\X509Credential $credential = null;

    public function __construct(private readonly string $entityId, private readonly string $certificatePath, private readonly string $keyPath, private readonly string $password)
    {
    }

    /**
     * @return CredentialInterface[]
     */
    public function getByEntityId(string $entityId): array
    {
        if ($entityId !== $this->entityId) {
            return [];
        }

        if (null == $this->credential) {
            $certificate = X509Certificate::fromFile($this->certificatePath);
            $this->credential = new X509Credential(
                $certificate,
                KeyHelper::createPrivateKey($this->keyPath, $this->password, true, $certificate->getSignatureAlgorithm())
            );
            $this->credential->setEntityId($this->entityId);
        }

        return [$this->credential];
    }
}
