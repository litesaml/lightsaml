<?php

namespace LightSaml\Store\Credential;

use LightSaml\Credential\CredentialInterface;
use LightSaml\Credential\KeyHelper;
use LightSaml\Credential\X509Certificate;
use LightSaml\Credential\X509Credential;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class X509FileCredentialStore implements CredentialStoreInterface
{
    private ?X509Credential $credential = null;

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
                KeyHelper::createPrivateKey($this->keyPath, $this->password, true, self::getSigningAlgorithm($certificate))
            );
            $this->credential->setEntityId($this->entityId);
        }

        return [$this->credential];
    }

    /**
     * The certificate's signature algorithm was chosen by its issuer, not for this key: only keep it
     * when it is a strong RSA one, and sign with RSA-SHA256 otherwise (SHA-1, MD5, ECDSA CA, ...).
     */
    private static function getSigningAlgorithm(X509Certificate $certificate): string
    {
        $algorithm = $certificate->getSignatureAlgorithm();

        return in_array($algorithm, [XMLSecurityKey::RSA_SHA256, XMLSecurityKey::RSA_SHA384, XMLSecurityKey::RSA_SHA512], true)
            ? $algorithm
            : XMLSecurityKey::RSA_SHA256;
    }
}
