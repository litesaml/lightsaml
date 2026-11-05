<?php

namespace Tests\Functional\Credential;

use LightSaml\Credential\KeyHelper;
use LightSaml\Credential\X509Certificate;
use LightSaml\Model\Assertion\Assertion;
use LightSaml\Model\Assertion\EncryptedAssertionReader;
use LightSaml\Model\Assertion\EncryptedAssertionWriter;
use LightSaml\Model\Assertion\Issuer;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use Tests\BaseTestCase;

class PrivateKeyMaterialTest extends BaseTestCase
{
    private const PASSPHRASE = 'secret';

    public function test_decrypts_assertion_with_password_protected_key()
    {
        $this->assertSame('_assertion', $this->roundTrip($this->protectedKey())->getId());
    }

    public function test_decrypts_assertion_with_unprotected_key()
    {
        $this->assertSame('_assertion', $this->roundTrip($this->unprotectedKey())->getId());
    }

    public function test_cast_private_key_verifies_signature()
    {
        $signer = new XMLSecurityKey(XMLSecurityKey::RSA_SHA512, ['type' => 'private']);
        $signer->loadKey($this->pem());
        $signature = $signer->signData('data');

        foreach ([$this->unprotectedKey(), $this->protectedKey()] as $private) {
            $public = KeyHelper::castKey($private, XMLSecurityKey::RSA_SHA512);

            $this->assertStringNotContainsString('PRIVATE KEY', $public->key);
            $this->assertSame(1, $public->verifySignature('data', $signature));
        }
    }

    public function test_cast_private_key_encrypts()
    {
        $public = KeyHelper::castKey($this->unprotectedKey(), XMLSecurityKey::RSA_OAEP_MGF1P);

        $private = new XMLSecurityKey(XMLSecurityKey::RSA_OAEP_MGF1P, ['type' => 'private']);
        $private->loadKey($this->pem());

        $this->assertSame('data', $private->decryptData($public->encryptData('data')));
    }

    private function roundTrip(XMLSecurityKey $privateKey): Assertion
    {
        $assertion = new Assertion();
        $assertion->setId('_assertion');
        $assertion->setIssuer(new Issuer('https://idp.example.com'));

        $certificate = X509Certificate::fromFile(__DIR__ . '/../../resources/web_saml.crt');

        $writer = new EncryptedAssertionWriter();
        $writer->encrypt($assertion, KeyHelper::createPublicKey($certificate));

        $serialized = new SerializationContext();
        $writer->serialize($serialized->getDocument(), $serialized);

        $context = new DeserializationContext();
        $context->getDocument()->loadXML($serialized->getDocument()->saveXML());

        $reader = new EncryptedAssertionReader();
        $reader->deserialize($context->getDocument()->firstChild, $context);

        return $reader->decryptAssertion($privateKey, new DeserializationContext());
    }

    private function unprotectedKey(): XMLSecurityKey
    {
        return KeyHelper::createPrivateKey($this->pem(), null);
    }

    private function protectedKey(): XMLSecurityKey
    {
        openssl_pkey_export(openssl_pkey_get_private($this->pem()), $encrypted, self::PASSPHRASE);

        return KeyHelper::createPrivateKey($encrypted, self::PASSPHRASE);
    }

    private function pem(): string
    {
        return file_get_contents(__DIR__ . '/../../resources/web_saml.key');
    }
}
