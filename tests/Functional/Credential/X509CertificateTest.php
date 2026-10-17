<?php

namespace Tests\Functional\Credential;

use LightSaml\Credential\KeyHelper;
use LightSaml\Credential\RsaPssKey;
use LightSaml\Credential\X509Certificate;
use LightSaml\Error\LightSamlSecurityException;
use LightSaml\SamlConstants;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use Tests\BaseTestCase;

class X509CertificateTest extends BaseTestCase
{
    public function test_get_name(): void
    {
        $certificate = new X509Certificate();
        $certificate->loadFromFile(__DIR__ . '/../../resources/saml.crt');
        $this->assertEquals('/C=RS/ST=Serbia/O=BOS/CN=mt.evo.team', $certificate->getName());
    }

    public function test_algorithm_sha1(): void
    {
        $certificate = X509Certificate::fromFile(__DIR__ . '/../../resources/saml-sha1.crt');
        $this->assertEquals(XMLSecurityKey::RSA_SHA1, $certificate->getSignatureAlgorithm());
    }

    public function test_algorithm_sha256(): void
    {
        $certificate = X509Certificate::fromFile(__DIR__ . '/../../resources/saml-sha256.crt');
        $this->assertEquals(XMLSecurityKey::RSA_SHA256, $certificate->getSignatureAlgorithm());
    }

    public function test_algorithm_sha384(): void
    {
        $certificate = X509Certificate::fromFile(__DIR__ . '/../../resources/saml-sha384.crt');
        $this->assertEquals(XMLSecurityKey::RSA_SHA384, $certificate->getSignatureAlgorithm());
    }

    public function test_algorithm_sha512(): void
    {
        $certificate = X509Certificate::fromFile(__DIR__ . '/../../resources/saml-sha512.crt');
        $this->assertEquals(XMLSecurityKey::RSA_SHA512, $certificate->getSignatureAlgorithm());
    }

    public function test_algorithm_md5(): void
    {
        $certificate = X509Certificate::fromFile(__DIR__ . '/../../resources/saml-md5.crt');
        $this->assertEquals(SamlConstants::XMLDSIG_DIGEST_MD5, $certificate->getSignatureAlgorithm());
    }

    public function test_algorithm_pss(): void
    {
        $certificate = X509Certificate::fromFile(__DIR__ . '/../../resources/saml-pss.crt');
        $this->assertEquals(SamlConstants::RSA_PSS, $certificate->getSignatureAlgorithm());
        $this->assertEquals('SHA256', $certificate->getPssHashAlgorithm());
    }

    public function test_create_public_key_from_pss_certificate(): void
    {
        $certificate = X509Certificate::fromFile(__DIR__ . '/../../resources/saml-pss.crt');
        $key = KeyHelper::createPublicKey($certificate);
        $this->assertInstanceOf(RsaPssKey::class, $key);
        $this->assertEquals(SamlConstants::RSA_PSS, $key->type);
    }

    public function test_create_public_key_from_certificate_issued_with_md5(): void
    {
        $certificate = X509Certificate::fromFile(__DIR__ . '/../../resources/saml-md5.crt');
        $key = KeyHelper::createPublicKey($certificate);
        $this->assertSame(XMLSecurityKey::RSA_SHA256, $key->type);
    }

    public function test_public_key_type_rsa(): void
    {
        $certificate = X509Certificate::fromFile(__DIR__ . '/../../resources/saml-sha256.crt');
        $this->assertSame(X509Certificate::PUBLIC_KEY_TYPE_RSA, $certificate->getPublicKeyType());
    }

    public function test_public_key_type_rsa_pss(): void
    {
        $certificate = X509Certificate::fromFile(__DIR__ . '/../../resources/saml-pss.crt');
        $this->assertSame(X509Certificate::PUBLIC_KEY_TYPE_RSA_PSS, $certificate->getPublicKeyType());
    }

    /**
     * @see https://github.com/litesaml/lightsaml/issues/123
     */
    public function test_rsa_certificate_issued_by_ecdsa_ca_falls_back_to_its_public_key_type(): void
    {
        $certificate = X509Certificate::fromFile(__DIR__ . '/../../resources/saml-rsa-ecdsa-ca.crt');
        $this->assertSame(X509Certificate::PUBLIC_KEY_TYPE_RSA, $certificate->getPublicKeyType());
        $this->assertSame(XMLSecurityKey::RSA_SHA256, $certificate->getSignatureAlgorithm());
    }

    public function test_create_public_key_from_rsa_certificate_issued_by_ecdsa_ca(): void
    {
        $certificate = X509Certificate::fromFile(__DIR__ . '/../../resources/saml-rsa-ecdsa-ca.crt');
        $key = KeyHelper::createPublicKey($certificate);
        $this->assertSame(XMLSecurityKey::RSA_SHA256, $key->type);
    }

    public function test_ec_certificate_issued_by_ecdsa_ca_is_rejected_with_its_key_type(): void
    {
        $this->expectException(LightSamlSecurityException::class);
        $this->expectExceptionMessage('Unsupported certificate public key type "EC": only RSA keys are supported');

        X509Certificate::fromFile(__DIR__ . '/../../resources/saml-ec-ecdsa-ca.crt');
    }

    public function test_ec_certificate_issued_by_rsa_ca_can_be_loaded(): void
    {
        $certificate = X509Certificate::fromFile(__DIR__ . '/../../resources/saml-ec-rsa-ca.crt');
        $this->assertSame(X509Certificate::PUBLIC_KEY_TYPE_EC, $certificate->getPublicKeyType());
    }

    public function test_create_public_key_from_ec_certificate_issued_by_rsa_ca_is_rejected(): void
    {
        $certificate = X509Certificate::fromFile(__DIR__ . '/../../resources/saml-ec-rsa-ca.crt');

        $this->expectException(LightSamlSecurityException::class);
        $this->expectExceptionMessage('Unsupported certificate public key type "EC": only RSA keys are supported');

        KeyHelper::createPublicKey($certificate);
    }

    public function test_get_subject(): void
    {
        $certificate = new X509Certificate();
        $certificate->loadFromFile(__DIR__ . '/../../resources/saml.crt');
        $this->assertEquals(
            ['C' => 'RS', 'ST' => 'Serbia', 'O' => 'BOS', 'CN' => 'mt.evo.team'],
            $certificate->getSubject()
        );
    }

    public function test_get_issuer(): void
    {
        $certificate = new X509Certificate();
        $certificate->loadFromFile(__DIR__ . '/../../resources/saml.crt');
        $this->assertEquals(
            ['C' => 'RS', 'ST' => 'Serbia', 'O' => 'BOS', 'CN' => 'mt.evo.team'],
            $certificate->getIssuer()
        );
    }

    public function test_get_valid_from_timestamp(): void
    {
        $certificate = new X509Certificate();
        $certificate->loadFromFile(__DIR__ . '/../../resources/saml.crt');
        $this->assertEquals(1381258772, $certificate->getValidFromTimestamp());
    }

    public function test_get_valid_to_timestamp(): void
    {
        $certificate = new X509Certificate();
        $certificate->loadFromFile(__DIR__ . '/../../resources/saml.crt');
        $this->assertEquals(1696791572, $certificate->getValidToTimestamp());
    }

    public function test_get_fingerprint(): void
    {
        $certificate = new X509Certificate();
        $certificate->loadFromFile(__DIR__ . '/../../resources/saml.crt');
        $this->assertEquals('9a092fb31216fd1a9af9427ffc98280bc30e2f81', $certificate->getFingerprint());
    }

    public function test_get_info(): void
    {
        $certificate = new X509Certificate();
        $certificate->loadFromFile(__DIR__ . '/../../resources/saml.crt');
        $info = $certificate->getInfo();
        $this->assertArrayHasKey('name', $info);
        $this->assertArrayHasKey('subject', $info);
        $this->assertArrayHasKey('serialNumber', $info);
        $this->assertArrayHasKey('validFrom', $info);
        $this->assertArrayHasKey('validTo', $info);
        $this->assertArrayHasKey('validFrom_time_t', $info);
        $this->assertArrayHasKey('validTo_time_t', $info);
    }
}
