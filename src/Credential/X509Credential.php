<?php

namespace LightSaml\Credential;

use RobRichards\XMLSecLibs\XMLSecurityKey;

class X509Credential extends AbstractCredential implements X509CredentialInterface
{
    /**
     */
    public function __construct(protected X509Certificate $certificate, ?XMLSecurityKey $privateKey = null)
    {
        parent::__construct();

        $this->setPublicKey(KeyHelper::createPublicKey($this->certificate));

        $this->setKeyNames([$this->getCertificate()->getName()]);

        if ($privateKey instanceof XMLSecurityKey) {
            $this->setPrivateKey($privateKey);
        }
    }

    /**
     * @return X509Certificate
     */
    public function getCertificate()
    {
        return $this->certificate;
    }
}
