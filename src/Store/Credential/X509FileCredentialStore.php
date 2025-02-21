<?php

namespace LightSaml\Store\Credential;

use LightSaml\Credential\CredentialInterface;
use LightSaml\Credential\KeyHelper;
use LightSaml\Credential\X509Certificate;
use LightSaml\Credential\X509Credential;

class X509FileCredentialStore implements CredentialStoreInterface
{
    /** @var X509Credential */
    private $credential;

    /**
     * @param string $entityId
     * @param string $certificatePath
     * @param string $keyPath
     * @param string $password
     */
    public function __construct(private $entityId, private $certificatePath, private $keyPath, private $password)
    {
    }

    /**
     * @param string $entityId
     *
     * @return CredentialInterface[]
     */
    public function getByEntityId($entityId)
    {
        if ($entityId != $this->entityId) {
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
