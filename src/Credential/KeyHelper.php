<?php

namespace LightSaml\Credential;

use InvalidArgumentException;
use LightSaml\Error\LightSamlSecurityException;
use LightSaml\SamlConstants;
use phpseclib3\Crypt\Common\PrivateKey;
use phpseclib3\Crypt\PublicKeyLoader;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class KeyHelper
{
    /**
     * @param string      $key        Key content or key filename
     * @param string|null $passphrase Passphrase for the private key
     * @param bool        $isFile     true if $key is a filename of the key
     * @param string      $type
     *
     * @return XMLSecurityKey
     */
    public static function createPrivateKey($key, $passphrase, $isFile = false, $type = XMLSecurityKey::RSA_SHA256)
    {
        $result = new XMLSecurityKey($type, ['type' => 'private']);
        // xmlseclibs 4 forwards the passphrase to phpseclib, which rejects null; normalise to ''.
        $result->passphrase = $passphrase ?? '';
        $result->loadKey($key, $isFile, false);

        return $result;
    }

    /**
     * @return XMLSecurityKey
     */
    public static function createPublicKey(X509Certificate $certificate)
    {
        $algo = $certificate->getSignatureAlgorithm();
        if (null == $algo) {
            throw new LightSamlSecurityException('Unrecognized certificate signature algorithm');
        }

        if ($algo === SamlConstants::RSA_PSS) {
            $hashAlgo = $certificate->getPssHashAlgorithm() ?? 'SHA256';
            $key = new RsaPssKey($hashAlgo);
            $key->loadKey($certificate->toPem(), false, true);

            return $key;
        }

        $key = new XMLSecurityKey($algo, ['type' => 'public']);
        $key->loadKey($certificate->toPem(), false, true);

        return $key;
    }

    /**
     * @param string $algorithm
     *
     * @throws LightSamlSecurityException
     * @throws InvalidArgumentException
     *
     * @return XMLSecurityKey
     */
    public static function castKey(XMLSecurityKey $key, $algorithm)
    {
        if (false == is_string($algorithm)) {
            throw new InvalidArgumentException('Algorithm must be string');
        }

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
     * Returns the PEM certificate/public-key material backing an XMLSecurityKey plus a
     * flag telling whether it is a certificate. A private key yields its public component.
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

        if (str_contains($material, 'PRIVATE KEY')) {
            $private = '' === $key->passphrase
                ? PublicKeyLoader::load($material)
                : PublicKeyLoader::load($material, $key->passphrase);

            if (!$private instanceof PrivateKey) {
                throw new LightSamlSecurityException('Unable to get public key from XMLSecurityKey.');
            }

            return [$private->getPublicKey()->toString('PKCS8'), false];
        }

        return [$material, str_contains($material, 'BEGIN CERTIFICATE')];
    }
}
