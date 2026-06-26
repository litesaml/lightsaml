<?php

namespace LightSaml\Credential;

interface X509CredentialInterface extends CredentialInterface
{
    public function getCertificate(): \LightSaml\Credential\X509Certificate;
}
