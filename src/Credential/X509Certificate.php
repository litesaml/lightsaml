<?php

namespace LightSaml\Credential;

use InvalidArgumentException;
use LightSaml\Error\LightSamlException;
use LightSaml\Error\LightSamlSecurityException;
use LightSaml\SamlConstants;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class X509Certificate
{
    private static array $typeMap = [
        'RSA-SHA1' => XMLSecurityKey::RSA_SHA1,
        'RSA-SHA256' => XMLSecurityKey::RSA_SHA256,
        'RSA-SHA384' => XMLSecurityKey::RSA_SHA384,
        'RSA-SHA512' => XMLSecurityKey::RSA_SHA512,
    ];

    protected string $data = '';

    protected ?array $info = null;

    private ?string $signatureAlgorithm = null;

    private ?string $pssHashAlgorithm = null;

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
        $signatureType = $this->info['signatureTypeSN'] ?? '';
        if ($signatureType && isset(self::$typeMap[$signatureType])) {
            $this->signatureAlgorithm = self::$typeMap[$signatureType];
        } else {
            openssl_x509_export($res, $out, false);
            if (preg_match('/^\s+Signature Algorithm:\s*(.*)\s*$/m', $out, $match)) {
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
                        $hashAlgo = 'SHA256';
                        if (preg_match('/^\s+Hash Algorithm:\s*(\S+)/m', $out, $hashMatch)) {
                            $normalized = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $hashMatch[1]));
                            $hashAlgo = match ($normalized) {
                                'SHA384', 'SHA2384' => 'SHA384',
                                'SHA512', 'SHA2512' => 'SHA512',
                                default => 'SHA256',
                            };
                        }
                        $this->pssHashAlgorithm = $hashAlgo;
                        $this->signatureAlgorithm = SamlConstants::RSA_PSS;
                        break;
                    default:
                }
            }
        }

        if (!$this->signatureAlgorithm) {
            throw new LightSamlSecurityException('Unrecognized signature algorithm');
        }
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
}
