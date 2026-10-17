<?php

namespace LightSaml\Credential;

use InvalidArgumentException;
use LightSaml\Error\LightSamlSecurityException;
use LightSaml\SamlConstants;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class KeyHelper
{
    private const SUPPORTED_PUBLIC_KEY_TYPES = [
        X509Certificate::PUBLIC_KEY_TYPE_RSA,
        X509Certificate::PUBLIC_KEY_TYPE_RSA_PSS,
    ];

    private const RSA_KEY_TYPES = [
        XMLSecurityKey::RSA_SHA1,
        XMLSecurityKey::RSA_SHA256,
        XMLSecurityKey::RSA_SHA384,
        XMLSecurityKey::RSA_SHA512,
    ];

    /**
     * @param string $key        Key content or key filename
     * @param string $passphrase Passphrase for the private key
     * @param bool   $isFile     true if $key is a filename of the key
     */
    public static function createPrivateKey(string $key, ?string $passphrase, bool $isFile = false, string $type = XMLSecurityKey::RSA_SHA256): XMLSecurityKey
    {
        $result = new XMLSecurityKey($type, ['type' => 'private']);
        // xmlseclibs 4 forwards the passphrase to phpseclib, which rejects null; normalise to ''.
        $result->passphrase = $passphrase ?? '';
        $result->loadKey($key, $isFile, false);

        return $result;
    }

    public static function createPublicKey(X509Certificate $certificate): RsaPssKey|XMLSecurityKey
    {
        $algo = $certificate->getSignatureAlgorithm();
        if (null == $algo) {
            throw new LightSamlSecurityException('Unrecognized certificate signature algorithm');
        }

        // The signature algorithm comes from the certificate's issuer: an RSA CA can issue e.g. EC certificates.
        $keyType = $certificate->getPublicKeyType();
        if (null !== $keyType && !in_array($keyType, self::SUPPORTED_PUBLIC_KEY_TYPES, true)) {
            throw new LightSamlSecurityException(sprintf(
                'Unsupported certificate public key type "%s": only RSA keys are supported',
                $keyType
            ));
        }

        if ($algo === SamlConstants::RSA_PSS) {
            $hashAlgo = $certificate->getPssHashAlgorithm() ?? 'SHA256';
            $key = new RsaPssKey($hashAlgo);
            $key->loadKey($certificate->toPem(), false, true);

            return $key;
        }

        // The issuer may have used an algorithm that is not an RSA key type (e.g. MD5): the key itself
        // is RSA, and it is re-typed to the document's SignatureMethod when verifying anyway.
        if (!in_array($algo, self::RSA_KEY_TYPES, true)) {
            $algo = X509Certificate::DEFAULT_RSA_ALGORITHM;
        }

        $key = new XMLSecurityKey($algo, ['type' => 'public']);
        $key->loadKey($certificate->toPem(), false, true);

        return $key;
    }

    /**
     *
     * @throws LightSamlSecurityException
     * @throws InvalidArgumentException
     */
    public static function castKey(XMLSecurityKey $key, string $algorithm): XMLSecurityKey|RsaPssKey
    {
        // do nothing if algorithm is already the type of the key
        if ($key->type === $algorithm) {
            return $key;
        }

        [$keyMaterial, $isCert] = self::extractPublicKeyMaterial($key);

        if ($algorithm === SamlConstants::RSA_PSS) {
            $hashAlgo = $key instanceof RsaPssKey ? $key->getPssDigest() : 'SHA256';
            $newKey = new RsaPssKey($hashAlgo);
            $newKey->loadKey($keyMaterial, false, $isCert);

            return $newKey;
        }

        $newKey = new XMLSecurityKey($algorithm, ['type' => 'public']);
        $newKey->loadKey($keyMaterial, false, $isCert);

        return $newKey;
    }

    /**
     * Returns the PEM certificate/public-key material backing a public XMLSecurityKey plus a
     * flag telling whether it is a certificate.
     *
     * Since xmlseclibs 4 the loaded material is kept as a PEM string in `$key->key`
     * (xmlseclibs 3 stored an OpenSSL resource there).
     *
     * @return array{0: string, 1: bool}
     *
     * @throws LightSamlSecurityException
     */
    private static function extractPublicKeyMaterial(XMLSecurityKey $key): array
    {
        $material = $key->key;

        if (!is_string($material) || '' === $material) {
            throw new LightSamlSecurityException('Unable to get key details from XMLSecurityKey.');
        }

        return [$material, str_contains($material, 'BEGIN CERTIFICATE')];
    }
}
