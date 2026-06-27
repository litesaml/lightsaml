<?php

namespace LightSaml\Meta\TrustOptions;

use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class TrustOptions
{
    protected bool $signAuthnRequest = false;

    protected bool $encryptAuthnRequest = false;

    protected bool $signAssertions = true;

    protected bool $encryptAssertions = true;

    protected bool $signResponse = true;

    protected string $signatureDigestAlgorithm = XMLSecurityDSig::SHA1;

    protected string $blockEncryptionAlgorithm = XMLSecurityKey::AES128_CBC;

    protected string $keyTransportEncryptionAlgorithm = XMLSecurityKey::RSA_OAEP_MGF1P;

    public function getEncryptAssertions(): bool
    {
        return $this->encryptAssertions;
    }

    public function setEncryptAssertions(bool $encryptAssertions): static
    {
        $this->encryptAssertions = $encryptAssertions;

        return $this;
    }

    public function getEncryptAuthnRequest(): bool
    {
        return $this->encryptAuthnRequest;
    }

    public function setEncryptAuthnRequest(bool $encryptAuthnRequest): static
    {
        $this->encryptAuthnRequest = $encryptAuthnRequest;

        return $this;
    }

    public function getSignAssertions(): bool
    {
        return $this->signAssertions;
    }

    public function setSignAssertions(bool $signAssertions): static
    {
        $this->signAssertions = $signAssertions;

        return $this;
    }

    public function getSignAuthnRequest(): bool
    {
        return $this->signAuthnRequest;
    }

    public function setSignAuthnRequest(bool $signAuthnRequest): static
    {
        $this->signAuthnRequest = $signAuthnRequest;

        return $this;
    }

    public function getSignResponse(): bool
    {
        return $this->signResponse;
    }

    public function setSignResponse(bool $signResponse): static
    {
        $this->signResponse = $signResponse;

        return $this;
    }

    public function getSignatureDigestAlgorithm(): string
    {
        return $this->signatureDigestAlgorithm;
    }

    public function setSignatureDigestAlgorithm(string $signatureDigestAlgorithm): static
    {
        $this->signatureDigestAlgorithm = $signatureDigestAlgorithm;

        return $this;
    }

    public function getBlockEncryptionAlgorithm(): string
    {
        return $this->blockEncryptionAlgorithm;
    }

    public function setBlockEncryptionAlgorithm(string $blockEncryptionAlgorithm): static
    {
        $this->blockEncryptionAlgorithm = $blockEncryptionAlgorithm;

        return $this;
    }

    public function getKeyTransportEncryptionAlgorithm(): string
    {
        return $this->keyTransportEncryptionAlgorithm;
    }

    public function setKeyTransportEncryptionAlgorithm(string $keyTransportEncryptionAlgorithm): static
    {
        $this->keyTransportEncryptionAlgorithm = $keyTransportEncryptionAlgorithm;

        return $this;
    }
}
