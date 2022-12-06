<?php

namespace LightSaml\Credential;

interface X509CredentialInterface extends CredentialInterface
{
    /**
     * @return X509Certificate
     */
    public function getCertificate();
}
