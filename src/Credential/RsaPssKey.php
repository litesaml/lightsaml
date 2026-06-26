<?php

namespace LightSaml\Credential;

use Exception;
use LightSaml\SamlConstants;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class RsaPssKey extends XMLSecurityKey
{
    private string $pssDigest;

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
     * @throws Exception
     */
    public function verifySignature($data, $signature): int
    {
        $keyDetails = openssl_pkey_get_details($this->key);

        if (false !== $keyDetails && $keyDetails['type'] !== OPENSSL_KEYTYPE_RSA) {
            // PSS-constrained key (type -1): openssl_verify enforces PSS padding automatically
            return openssl_verify($data, $signature, $this->key, $this->pssDigest);
        }

        // Regular RSA key: use manual EMSA-PSS-VERIFY (RFC 8017 §9.1.2)
        return $this->emsaPssVerify($data, $signature) ? 1 : 0;
    }

    private function emsaPssVerify(string $message, string $signature): bool
    {
        $keyDetails = openssl_pkey_get_details($this->key);
        if (false === $keyDetails) {
            return false;
        }

        $emBits = $keyDetails['bits'] - 1;
        $emLen = (int) ceil($emBits / 8);

        if (!openssl_public_decrypt($signature, $em, $this->key, OPENSSL_NO_PADDING)) {
            return false;
        }

        $em = str_pad($em, $emLen, "\x00", STR_PAD_LEFT);

        $mHash = hash($this->pssDigest, $message, true);
        $hLen = strlen($mHash);

        if (strlen($em) < $hLen + 2 || ord($em[strlen($em) - 1]) !== 0xBC) {
            return false;
        }

        $maskedDB = substr($em, 0, $emLen - $hLen - 1);
        $h = substr($em, $emLen - $hLen - 1, $hLen);
        $dbLen = $emLen - $hLen - 1;

        $topBits = 8 * $emLen - $emBits;
        if ($topBits > 0 && (ord($maskedDB[0]) >> (8 - $topBits)) !== 0) {
            return false;
        }

        $db = $maskedDB ^ $this->mgf1($h, $dbLen);

        if ($topBits > 0) {
            $db[0] = chr(ord($db[0]) & (0xFF >> $topBits));
        }

        // Find 0x01 separator: DB = PS (zero or more 0x00 bytes) || 0x01 || salt
        $pos = 0;
        while ($pos < $dbLen - 1 && $db[$pos] === "\x00") {
            $pos++;
        }

        if (ord($db[$pos]) !== 0x01) {
            return false;
        }

        $salt = substr($db, $pos + 1);
        $mPrime = "\x00\x00\x00\x00\x00\x00\x00\x00" . $mHash . $salt;

        return hash_equals($h, hash($this->pssDigest, $mPrime, true));
    }

    private function mgf1(string $seed, int $length): string
    {
        $hashLen = strlen(hash($this->pssDigest, '', true));
        $output = '';
        for ($i = 0; $i < (int) ceil($length / $hashLen); $i++) {
            $output .= hash($this->pssDigest, $seed . pack('N', $i), true);
        }

        return substr($output, 0, $length);
    }
}
