<?php

namespace LightSaml\Credential;

use RobRichards\XMLSecLibs\XMLSecurityKey;

class X509Credential extends AbstractCredential implements X509CredentialInterface
{
    /**
     * @param XMLSecurityKey $privateKey
     */
    public function __construct(protected \LightSaml\Credential\X509Certificate $certificate, ?XMLSecurityKey $privateKey = null)
    {
        parent::__construct();

        $this->setPublicKey(KeyHelper::createPublicKey($this->certificate));

        $this->setKeyNames([$this->getCertificate()->getName()]);

        if ($privateKey instanceof \RobRichards\XMLSecLibs\XMLSecurityKey) {
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
