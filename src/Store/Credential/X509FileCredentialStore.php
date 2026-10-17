<?php

namespace LightSaml\Store\Credential;

use LightSaml\Credential\CredentialInterface;
use LightSaml\Credential\KeyHelper;
use LightSaml\Credential\X509Certificate;
use LightSaml\Credential\X509Credential;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class X509FileCredentialStore implements CredentialStoreInterface
{
    private const DEFAULT_SIGNING_ALGORITHM = XMLSecurityKey::RSA_SHA256;

    private const ISSUER_ALGORITHMS_KEPT_FOR_SIGNING = [
        XMLSecurityKey::RSA_SHA256,
        XMLSecurityKey::RSA_SHA384,
        XMLSecurityKey::RSA_SHA512,
    ];

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
     * when it is a strong RSA one, and sign with DEFAULT_SIGNING_ALGORITHM otherwise (SHA-1, MD5, ...).
     */
    private static function getSigningAlgorithm(X509Certificate $certificate): string
    {
        $algorithm = $certificate->getSignatureAlgorithm();

        return in_array($algorithm, self::ISSUER_ALGORITHMS_KEPT_FOR_SIGNING, true)
            ? $algorithm
            : self::DEFAULT_SIGNING_ALGORITHM;
    }
}
