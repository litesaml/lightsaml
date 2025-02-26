<?php

namespace LightSaml\Meta;

use LightSaml\Credential\X509Certificate;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class SigningOptions
{
    public const CERTIFICATE_SUBJECT_NAME = 'subjectName';
    public const CERTIFICATE_ISSUER_SERIAL = 'issuerSerial';

    /** @var bool */
    private $enabled = true;

    private readonly ParameterBag $certificateOptions;

    /**
     */
    public function __construct(private ?XMLSecurityKey $privateKey = null, private ?X509Certificate $certificate = null)
    {
        $this->certificateOptions = new ParameterBag();
    }

    /**
     * @return X509Certificate
     */
    public function getCertificate()
    {
        return $this->certificate;
    }

    /**
     *
     * @return SigningOptions
     */
    public function setCertificate(?X509Certificate $certificate = null)
    {
        $this->certificate = $certificate;

        return $this;
    }

    /**
     * @return XMLSecurityKey
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     *
     * @return SigningOptions
     */
    public function setPrivateKey(?XMLSecurityKey $privateKey = null)
    {
        $this->privateKey = $privateKey;

        return $this;
    }

    /**
     * @return ParameterBag
     */
    public function getCertificateOptions()
    {
        return $this->certificateOptions;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     *
     * @return SigningOptions
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (bool) $enabled;

        return $this;
    }
}
