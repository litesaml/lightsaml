<?php

namespace LightSaml\Credential;

use InvalidArgumentException;
use LightSaml\Error\LightSamlSecurityException;
use LightSaml\SamlConstants;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class KeyHelper
{
    /**
     * @param string $key        Key content or key filename
     * @param string $passphrase Passphrase for the private key
     * @param bool   $isFile     true if $key is a filename of the key
     */
    public static function createPrivateKey(string $key, ?string $passphrase, bool $isFile = false, string $type = XMLSecurityKey::RSA_SHA256): XMLSecurityKey
    {
        $result = new XMLSecurityKey($type, ['type' => 'private']);
        $result->passphrase = $passphrase;
        $result->loadKey($key, $isFile, false);

        return $result;
    }

    public static function createPublicKey(X509Certificate $certificate): RsaPssKey|XMLSecurityKey
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
     *
     * @throws LightSamlSecurityException
     * @throws InvalidArgumentException
     */
    public static function castKey(XMLSecurityKey $key, string $algorithm): XMLSecurityKey|RsaPssKey
    {
        if (false == is_string($algorithm)) {
            throw new InvalidArgumentException('Algorithm must be string');
        }

        // do nothing if algorithm is already the type of the key
        if ($key->type === $algorithm) {
            return $key;
        }

        $keyInfo = openssl_pkey_get_details($key->key);
        if (false === $keyInfo) {
            throw new LightSamlSecurityException('Unable to get key details from XMLSecurityKey.');
        }
        if (false == isset($keyInfo['key'])) {
            throw new LightSamlSecurityException('Missing key in public key details.');
        }

        if ($algorithm === SamlConstants::RSA_PSS) {
            $hashAlgo = $key instanceof RsaPssKey ? $key->getPssDigest() : 'SHA256';
            $newKey = new RsaPssKey($hashAlgo);
            $newKey->loadKey($keyInfo['key']);

            return $newKey;
        }

        $newKey = new XMLSecurityKey($algorithm, ['type' => 'public']);
        $newKey->loadKey($keyInfo['key']);

        return $newKey;
    }
}
