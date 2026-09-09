<?php

namespace LightSaml\Credential;

use Exception;
use LightSaml\SamlConstants;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA;
use RobRichards\XMLSecLibs\XMLSecurityKey;

/**
 * RSASSA-PSS verification key for XML signatures.
 *
 * SAML uses a single SignatureMethod URI (http://www.w3.org/2007/05/xmldsig-more#rsa-pss)
 * with the digest carried separately in DigestMethod, so this key is not tied to one of
 * xmlseclibs' fixed *-rsa-MGF1 URIs. Verification is delegated to phpseclib with the
 * MGF-1 hash pinned to the signature digest and the salt length equal to the digest
 * length, matching the XML Signature RSA-PSS profile (RFC 9231).
 */
class RsaPssKey extends XMLSecurityKey
{
    private readonly string $pssDigest;

    public function __construct(string $hashAlgorithm = 'SHA256')
    {
        $rsaType = match (strtoupper($hashAlgorithm)) {
            'SHA384' => XMLSecurityKey::RSA_SHA384,
            'SHA512' => XMLSecurityKey::RSA_SHA512,
            default => XMLSecurityKey::RSA_SHA256,
        };
        parent::__construct($rsaType, ['type' => 'public']);
        $this->type = SamlConstants::RSA_PSS;
        $this->pssDigest = strtolower($hashAlgorithm);
    }

    public function getPssDigest(): string
    {
        return $this->pssDigest;
    }

    /**
     * @param string $data
     * @param string $signature
     *
     * @throws Exception
     */
    public function verifySignature($data, $signature): int
    {
        if (!is_string($this->key) || '' === $this->key) {
            throw new Exception('No key loaded for RSA-PSS verification');
        }

        $key = PublicKeyLoader::load($this->key);
        if (!$key instanceof RSA) {
            throw new Exception('Expected an RSA key for RSA-PSS verification');
        }

        $key = $key
            ->withPadding(RSA::SIGNATURE_PSS)
            ->withHash($this->pssDigest)
            ->withMGFHash($this->pssDigest)
            ->withSaltLength(strlen(hash($this->pssDigest, '', true)));

        return $key->verify($data, $signature) ? 1 : 0;
    }
}
