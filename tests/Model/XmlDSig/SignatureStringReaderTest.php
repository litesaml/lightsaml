<?php

namespace Tests\Model\XmlDSig;

use LightSaml\Credential\KeyHelper;
use LightSaml\Credential\X509Certificate;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LightSaml\Model\XmlDSig\AbstractSignatureReader;
use LightSaml\Model\XmlDSig\SignatureStringReader;
use LogicException;
use Tests\BaseTestCase;

class SignatureStringReaderTest extends BaseTestCase
{
    public function test_can_be_constructed_without_arguments(): void
    {
        new SignatureStringReader();
        $this->assertTrue(true);
    }

    public function test_can_be_constructed_with_signature_algorithm_and_data(): void
    {
        new SignatureStringReader('signature', 'algo', 'data');
        $this->assertTrue(true);
    }

    public function test_extends_abstract_signature_reader(): void
    {
        $reader = new SignatureStringReader();
        $this->assertInstanceOf(AbstractSignatureReader::class, $reader);
    }

    public function test_validate_returns_false_when_no_signature_set(): void
    {
        $publicKey = KeyHelper::createPublicKey(X509Certificate::fromFile(__DIR__ . '/../../resources/saml.crt'));
        $reader = new SignatureStringReader();
        $result = $reader->validate($publicKey);
        $this->assertFalse($result);
    }

    public function test_validate_correct_signature(): void
    {
        $publicKey = KeyHelper::createPublicKey(X509Certificate::fromFile(__DIR__ . '/../../resources/saml.crt'));
        $privateKey = KeyHelper::createPrivateKey(__DIR__ . '/../../resources/saml.pem', '', true);
        $data = 'Some message data';
        $signature = base64_encode($privateKey->signData($data));

        $reader = new SignatureStringReader($signature, $privateKey->type, $data);
        $result = $reader->validate($publicKey);
        $this->assertTrue($result);
    }

    public function test_serialize_throws_exception(): void
    {
        $this->expectExceptionMessage("SignatureStringReader can not be serialized");
        $this->expectException(LogicException::class);
        $context = new SerializationContext();
        $reader = new SignatureStringReader();
        $reader->serialize($context->getDocument(), $context);
    }

    public function test_deserialize_throws_exception(): void
    {
        $this->expectExceptionMessage("SignatureStringReader can not be deserialized");
        $this->expectException(LogicException::class);
        $context = new DeserializationContext();
        $reader = new SignatureStringReader();
        $reader->deserialize($context->getDocument(), $context);
    }
}
