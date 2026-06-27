<?php

namespace LightSaml\Credential;

use LightSaml\Credential\Context\CredentialContextSet;
use RobRichards\XMLSecLibs\XMLSecurityKey;

abstract class AbstractCredential implements CredentialInterface
{
    private ?string $entityId = null;

    private ?string $usageType = null;

    /** @var string[] */
    private array $keyNames = [];

    private ?\RobRichards\XMLSecLibs\XMLSecurityKey $publicKey = null;

    private ?\RobRichards\XMLSecLibs\XMLSecurityKey $privateKey = null;

    private ?string $secretKey = null;

    private CredentialContextSet $credentialContext;

    public function __construct()
    {
        $this->credentialContext = new CredentialContextSet();
    }

    public function getEntityId(): string
    {
        return $this->entityId;
    }

    /**
     * One of UsageType constants.
     */
    public function getUsageType(): ?string
    {
        return $this->usageType;
    }

    /**
     * @return string[]
     */
    public function getKeyNames(): array
    {
        return $this->keyNames;
    }

    public function getPublicKey(): ?\RobRichards\XMLSecLibs\XMLSecurityKey
    {
        return $this->publicKey;
    }

    public function getPrivateKey(): ?\RobRichards\XMLSecLibs\XMLSecurityKey
    {
        return $this->privateKey;
    }

    public function getSecretKey(): ?string
    {
        return $this->secretKey;
    }

    public function getCredentialContext(): \LightSaml\Credential\Context\CredentialContextSet
    {
        return $this->credentialContext;
    }

    public function setCredentialContext(CredentialContextSet $credentialContext): \LightSaml\Credential\AbstractCredential
    {
        $this->credentialContext = $credentialContext;

        return $this;
    }

    
    public function setEntityId(string $entityId): \LightSaml\Credential\AbstractCredential
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * @param string[] $keyNames
     */
    public function setKeyNames(array $keyNames): \LightSaml\Credential\AbstractCredential
    {
        $this->keyNames = $keyNames;

        return $this;
    }

    
    public function addKeyName(string $keyName): \LightSaml\Credential\AbstractCredential
    {
        $keyName = trim($keyName);
        if ($keyName !== '' && $keyName !== '0') {
            $this->keyNames[] = $keyName;
        }

        return $this;
    }

    public function setPrivateKey(XMLSecurityKey $privateKey): \LightSaml\Credential\AbstractCredential
    {
        $this->privateKey = $privateKey;

        return $this;
    }

    public function setPublicKey(XMLSecurityKey $publicKey): \LightSaml\Credential\AbstractCredential
    {
        $this->publicKey = $publicKey;

        return $this;
    }

    
    public function setSecretKey(?string $secretKey): \LightSaml\Credential\AbstractCredential
    {
        $this->secretKey = $secretKey;

        return $this;
    }

    
    public function setUsageType(string $usageType): \LightSaml\Credential\AbstractCredential
    {
        $this->usageType = $usageType;

        return $this;
    }
}
