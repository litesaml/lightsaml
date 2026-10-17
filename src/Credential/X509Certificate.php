<?php

namespace LightSaml\Credential;

use InvalidArgumentException;
use LightSaml\Error\LightSamlException;
use LightSaml\Error\LightSamlSecurityException;
use LightSaml\SamlConstants;
use OpenSSLCertificate;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class X509Certificate
{
    public const PUBLIC_KEY_TYPE_RSA = 'RSA';
    public const PUBLIC_KEY_TYPE_RSA_PSS = 'RSA-PSS';
    public const PUBLIC_KEY_TYPE_EC = 'EC';
    public const PUBLIC_KEY_TYPE_DSA = 'DSA';

    /**
     * Algorithm used for an RSA key when the issuer's algorithm is not an RSA one.
     */
    public const DEFAULT_RSA_ALGORITHM = XMLSecurityKey::RSA_SHA256;

    /** @var array<string, string> */
    private static array $typeMap = [
        'RSA-SHA1' => XMLSecurityKey::RSA_SHA1,
        'RSA-SHA256' => XMLSecurityKey::RSA_SHA256,
        'RSA-SHA384' => XMLSecurityKey::RSA_SHA384,
        'RSA-SHA512' => XMLSecurityKey::RSA_SHA512,
    ];

    protected string $data = '';

    /** @var array<string, mixed>|null */
    protected ?array $info = null;

    private ?string $signatureAlgorithm = null;

    private ?string $pssHashAlgorithm = null;

    private ?string $publicKeyType = null;

    public static function fromFile(string $filename): self
    {
        $result = new self();
        $result->loadFromFile($filename);

        return $result;
    }

    public function setData(string $data): static
    {
        $this->data = preg_replace('/\s+/', '', $data);
        $this->parse();

        return $this;
    }

    public function getData(): string
    {
        return $this->data;
    }

    /**
     *
     * @throws InvalidArgumentException
     */
    public function loadPem(string $data): static
    {
        $pattern = '/^-----BEGIN CERTIFICATE-----([^-]*)^-----END CERTIFICATE-----/m';
        if (false == preg_match($pattern, $data, $matches)) {
            throw new InvalidArgumentException('Invalid PEM encoded certificate');
        }
        $this->data = preg_replace('/\s+/', '', $matches[1]);
        $this->parse();

        return $this;
    }

    /**
     *
     * @throws InvalidArgumentException
     */
    public function loadFromFile(string $filename): static
    {
        if (!is_file($filename)) {
            throw new InvalidArgumentException(sprintf("File not found '%s'", $filename));
        }
        $content = file_get_contents($filename);
        $this->loadPem($content);

        return $this;
    }

    public function toPem(): string
    {
        return "-----BEGIN CERTIFICATE-----\n" . chunk_split($this->getData(), 64, "\n") . "-----END CERTIFICATE-----\n";
    }

    public function parse(): void
    {
        if (false == $this->data) {
            throw new LightSamlException('Certificate data not set');
        }

        $res = openssl_x509_read($this->toPem());
        $this->info = openssl_x509_parse($res);
        $this->signatureAlgorithm = null;
        $this->pssHashAlgorithm = null;
        openssl_x509_export($res, $out, false);
        $this->publicKeyType = $this->detectPublicKeyType($res, $out);

        // See getSignatureAlgorithm(): the issuer's algorithm first, then the default for the key type.
        $this->detectIssuerSignatureAlgorithm($out);
        if (!$this->signatureAlgorithm) {
            $this->useDefaultAlgorithmForPublicKeyType($out);
        }

        if (!$this->signatureAlgorithm) {
            throw new LightSamlSecurityException(sprintf(
                'Unsupported certificate public key type "%s": only RSA keys are supported',
                $this->publicKeyType ?? 'unknown'
            ));
        }
    }

    /**
     * Algorithm the issuer used to sign this certificate, when it is an RSA one.
     */
    private function detectIssuerSignatureAlgorithm(string $text): void
    {
        $signatureType = $this->info['signatureTypeSN'] ?? '';
        if ($signatureType && isset(self::$typeMap[$signatureType])) {
            $this->signatureAlgorithm = self::$typeMap[$signatureType];

            return;
        }

        if (!preg_match('/^\s+Signature Algorithm:\s*(.*)\s*$/m', $text, $match)) {
            return;
        }

        switch (rtrim($match[1])) {
            case 'sha1WithRSAEncryption':
            case 'sha1WithRSA':
                $this->signatureAlgorithm = XMLSecurityKey::RSA_SHA1;
                break;
            case 'sha256WithRSAEncryption':
            case 'sha256WithRSA':
                $this->signatureAlgorithm = XMLSecurityKey::RSA_SHA256;
                break;
            case 'sha384WithRSAEncryption':
            case 'sha384WithRSA':
                $this->signatureAlgorithm = XMLSecurityKey::RSA_SHA384;
                break;
            case 'sha512WithRSAEncryption':
            case 'sha512WithRSA':
                $this->signatureAlgorithm = XMLSecurityKey::RSA_SHA512;
                break;
            case 'md5WithRSAEncryption':
            case 'md5WithRSA':
                $this->signatureAlgorithm = SamlConstants::XMLDSIG_DIGEST_MD5;
                break;
            case 'rsassaPss':
                $this->pssHashAlgorithm = $this->detectPssHashAlgorithm($text);
                $this->signatureAlgorithm = SamlConstants::RSA_PSS;
                break;
            default:
        }
    }

    /**
     * Used when the issuer's algorithm is not an RSA one (e.g. an ECDSA CA issuing an RSA certificate):
     * it says nothing about the certificate's own key, so pick a default algorithm for that key.
     */
    private function useDefaultAlgorithmForPublicKeyType(string $text): void
    {
        if (self::PUBLIC_KEY_TYPE_RSA === $this->publicKeyType) {
            $this->signatureAlgorithm = self::DEFAULT_RSA_ALGORITHM;
        } elseif (self::PUBLIC_KEY_TYPE_RSA_PSS === $this->publicKeyType) {
            $this->pssHashAlgorithm = $this->detectPssHashAlgorithm($text);
            $this->signatureAlgorithm = SamlConstants::RSA_PSS;
        }
    }

    private function detectPublicKeyType(OpenSSLCertificate $certificate, string $text): ?string
    {
        $publicKey = openssl_pkey_get_public($certificate);
        $details = $publicKey ? openssl_pkey_get_details($publicKey) : false;

        switch ($details ? $details['type'] : null) {
            case OPENSSL_KEYTYPE_RSA:
                return self::PUBLIC_KEY_TYPE_RSA;
            case OPENSSL_KEYTYPE_EC:
                return self::PUBLIC_KEY_TYPE_EC;
            case OPENSSL_KEYTYPE_DSA:
                return self::PUBLIC_KEY_TYPE_DSA;
        }

        // PHP does not map RSASSA-PSS (or e.g. Ed25519) keys to an OPENSSL_KEYTYPE_* constant.
        if (preg_match('/^\s+Public Key Algorithm:\s*(\S+)/m', $text, $match)) {
            return 'rsassaPss' === $match[1] ? self::PUBLIC_KEY_TYPE_RSA_PSS : $match[1];
        }

        return null;
    }

    private function detectPssHashAlgorithm(string $text): string
    {
        if (preg_match('/^\s+Hash Algorithm:\s*(\S+)/m', $text, $hashMatch)) {
            $normalized = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $hashMatch[1]));

            return match ($normalized) {
                'SHA384', 'SHA2384' => 'SHA384',
                'SHA512', 'SHA2512' => 'SHA512',
                default => 'SHA256',
            };
        }

        return 'SHA256';
    }

    /**
     * @throws LightSamlException
     */
    public function getName(): string
    {
        if (false == $this->info) {
            throw new LightSamlException('Certificate data not set');
        }

        return $this->info['name'];
    }

    /**
     * @throws LightSamlException
     */
    /** @return array<string, string|array<string, string>> */
    public function getSubject(): array
    {
        if (false == $this->info) {
            throw new LightSamlException('Certificate data not set');
        }

        return $this->info['subject'];
    }

    /**
     * @throws LightSamlException
     */
    /** @return array<string, string|array<string, string>> */
    public function getIssuer(): array
    {
        if (false == $this->info) {
            throw new LightSamlException('Certificate data not set');
        }

        return $this->info['issuer'];
    }

    /**
     * @throws LightSamlException
     */
    public function getValidFromTimestamp(): int
    {
        if (false == $this->info) {
            throw new LightSamlException('Certificate data not set');
        }

        return $this->info['validFrom_time_t'];
    }

    /**
     * @throws LightSamlException
     */
    public function getValidToTimestamp(): int
    {
        if (false == $this->info) {
            throw new LightSamlException('Certificate data not set');
        }

        return $this->info['validTo_time_t'];
    }

    /**
     * @throws LightSamlException
     */
    /** @return array<string, mixed> */
    public function getInfo(): array
    {
        if (false == $this->info) {
            throw new LightSamlException('Certificate data not set');
        }

        return $this->info;
    }

    /**
     * @throws LightSamlException
     */
    public function getFingerprint(): string
    {
        if (false == $this->data) {
            throw new LightSamlException('Certificate data not set');
        }

        return XMLSecurityKey::getRawThumbprint($this->toPem());
    }

    /**
     * XML-DSig algorithm associated with this certificate:
     * - the algorithm its issuer used to sign it, when that is an RSA algorithm (RSA-SHA1/256/384/512,
     *   RSA-PSS, or the MD5 digest URI for MD5);
     * - otherwise DEFAULT_RSA_ALGORITHM (or RSA-PSS) depending on the certificate's own key type.
     *
     * An RSA key can sign with any of these algorithms: this is not necessarily the algorithm SAML
     * messages are signed with. Signature verification uses the document's SignatureMethod instead.
     */
    public function getSignatureAlgorithm(): ?string
    {
        if (false == $this->data) {
            throw new LightSamlException('Certificate data not set');
        }

        return $this->signatureAlgorithm;
    }

    public function getPssHashAlgorithm(): ?string
    {
        return $this->pssHashAlgorithm;
    }

    /**
     * Type of the certificate's own public key: one of the PUBLIC_KEY_TYPE_* constants, or the
     * OpenSSL name of the key algorithm (e.g. "ED25519") for other types.
     */
    public function getPublicKeyType(): ?string
    {
        if (false == $this->data) {
            throw new LightSamlException('Certificate data not set');
        }

        return $this->publicKeyType;
    }
}
