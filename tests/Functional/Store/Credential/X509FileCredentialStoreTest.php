<?php

namespace Tests\Functional\Store\Credential;

use DateTime;
use LightSaml\Context\Model\DeserializationContext;
use LightSaml\Context\Model\SerializationContext;
use LightSaml\Credential\CredentialInterface;
use LightSaml\Credential\KeyHelper;
use LightSaml\Credential\X509Certificate;
use LightSaml\Error\LightSamlSecurityException;
use LightSaml\Helper;
use LightSaml\Model\Assertion\Issuer;
use LightSaml\Model\Protocol\Response;
use LightSaml\Model\XmlDSig\SignatureWriter;
use LightSaml\Model\XmlDSig\SignatureXmlReader;
use LightSaml\Store\Credential\X509FileCredentialStore;
use PHPUnit\Framework\Attributes\DataProvider;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use Tests\BaseTestCase;

class X509FileCredentialStoreTest extends BaseTestCase
{
    public function test_returns_null_if_entity_id_does_not_match(): void
    {
        $store = new X509FileCredentialStore('foo', '', '', '');
        $this->assertCount(0, $store->getByEntityId('bar'));
    }

    public function test_returns_credential_with_loaded_specified_key_and_certificate(): void
    {
        $store = new X509FileCredentialStore(
            $entityId = 'foo',
            __DIR__ . '/../../../resources/saml.crt',
            __DIR__ . '/../../../resources/saml.pem',
            ''
        );
        $arr = $store->getByEntityId($entityId);

        $this->assertCount(1, $arr);
        $this->assertInstanceOf(CredentialInterface::class, $arr[0]);
        $this->assertEquals($entityId, $arr[0]->getEntityId());
        $this->assertNotNull($arr[0]->getPrivateKey());
        $this->assertNotNull($arr[0]->getPublicKey());
    }

    /**
     * The certificate's own signature algorithm is chosen by its issuer: it is only kept as the
     * signing algorithm when it is a strong RSA one.
     */
    #[DataProvider('signingAlgorithmProvider')]
    public function test_signing_algorithm_does_not_follow_a_weak_or_unsupported_issuer_algorithm(string $certificate, string $key, string $expectedAlgorithm): void
    {
        $store = new X509FileCredentialStore(
            'foo',
            __DIR__ . '/../../../resources/' . $certificate,
            __DIR__ . '/../../../resources/' . $key,
            ''
        );

        $this->assertSame($expectedAlgorithm, $store->getByEntityId('foo')[0]->getPrivateKey()->type);
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function signingAlgorithmProvider(): array
    {
        return [
            'sha1 issuer' => ['saml-sha1.crt', 'saml.pem', XMLSecurityKey::RSA_SHA256],
            'md5 issuer' => ['saml-md5.crt', 'saml.pem', XMLSecurityKey::RSA_SHA256],
            'sha256 issuer' => ['saml-sha256.crt', 'saml.pem', XMLSecurityKey::RSA_SHA256],
            'sha384 issuer' => ['saml-sha384.crt', 'saml.pem', XMLSecurityKey::RSA_SHA384],
            'sha512 issuer' => ['saml-sha512.crt', 'saml.pem', XMLSecurityKey::RSA_SHA512],
            'ecdsa issuer' => ['saml-rsa-ecdsa-ca.crt', 'saml-rsa-ecdsa-ca.key', XMLSecurityKey::RSA_SHA256],
        ];
    }

    public function test_ec_certificate_is_rejected_with_its_key_type(): void
    {
        $store = new X509FileCredentialStore(
            'foo',
            __DIR__ . '/../../../resources/saml-ec-rsa-ca.crt',
            __DIR__ . '/../../../resources/saml.pem',
            ''
        );

        $this->expectException(LightSamlSecurityException::class);
        $this->expectExceptionMessage('Unsupported certificate public key type "EC"');

        $store->getByEntityId('foo');
    }

    /**
     * @see https://github.com/litesaml/lightsaml/issues/123
     */
    public function test_signature_with_rsa_certificate_issued_by_ecdsa_ca_can_be_verified(): void
    {
        $store = new X509FileCredentialStore(
            'foo',
            __DIR__ . '/../../../resources/saml-rsa-ecdsa-ca.crt',
            __DIR__ . '/../../../resources/saml-rsa-ecdsa-ca.key',
            ''
        );
        $certificate = X509Certificate::fromFile(__DIR__ . '/../../../resources/saml-rsa-ecdsa-ca.crt');

        $response = (new Response())
            ->setID(Helper::generateID())
            ->setIssueInstant(new DateTime())
            ->setIssuer(new Issuer('foo'))
            ->setSignature(new SignatureWriter($certificate, $store->getByEntityId('foo')[0]->getPrivateKey()));

        $serCtx = new SerializationContext();
        $response->serialize($serCtx->getDocument(), $serCtx);

        $desCtx = new DeserializationContext();
        $desCtx->getDocument()->loadXML($serCtx->getDocument()->saveXML());
        $deserialized = new Response();
        $deserialized->deserialize($desCtx->getDocument(), $desCtx);

        /** @var SignatureXmlReader $signature */
        $signature = $deserialized->getSignature();
        $this->assertSame(XMLSecurityKey::RSA_SHA256, $signature->getAlgorithm());
        $this->assertTrue($signature->validate(KeyHelper::createPublicKey($certificate)));
    }
}
