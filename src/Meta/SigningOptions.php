<?php

namespace LightSaml\Meta;

use LightSaml\Credential\X509Certificate;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class SigningOptions
{
    public const CERTIFICATE_SUBJECT_NAME = 'subjectName';
    public const CERTIFICATE_ISSUER_SERIAL = 'issuerSerial';

    private bool $enabled = true;

    private readonly ParameterBag $certificateOptions;

    /**
     */
    public function __construct(private ?XMLSecurityKey $privateKey = null, private ?X509Certificate $certificate = null)
    {
        $this->certificateOptions = new ParameterBag();
    }

    /**
     */
    public function getCertificate(): ?X509Certificate
    {
        return $this->certificate;
    }

    public function setCertificate(?X509Certificate $certificate = null): static
    {
        $this->certificate = $certificate;

        return $this;
    }

    /**
     */
    public function getPrivateKey(): ?XMLSecurityKey
    {
        return $this->privateKey;
    }

    public function setPrivateKey(?XMLSecurityKey $privateKey = null): static
    {
        $this->privateKey = $privateKey;

        return $this;
    }

    public function getCertificateOptions(): ParameterBag
    {
        return $this->certificateOptions;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }
}
