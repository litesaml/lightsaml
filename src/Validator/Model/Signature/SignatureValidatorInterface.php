<?php

namespace LightSaml\Validator\Model\Signature;

use LightSaml\Credential\CredentialInterface;
use LightSaml\Model\XmlDSig\AbstractSignatureReader;

interface SignatureValidatorInterface
{
    
    public function validate(AbstractSignatureReader $signature, string $issuer, string $metadataType): ?\LightSaml\Credential\CredentialInterface;
}
