<?php

namespace Tests\Functional\Model\XmlDSig;

use LightSaml\Context\Model\DeserializationContext;
use LightSaml\Context\Model\SerializationContext;
use LightSaml\Credential\KeyHelper;
use LightSaml\Credential\X509Certificate;
use LightSaml\Credential\X509Credential;
use LightSaml\Error\LightSamlSecurityException;
use LightSaml\Helper;
use LightSaml\Model\Assertion\Assertion;
use LightSaml\Model\Assertion\Issuer;
use LightSaml\Model\Assertion\NameID;
use LightSaml\Model\Assertion\Subject;
use LightSaml\Model\Protocol\Response;
use LightSaml\Model\Protocol\Status;
use LightSaml\Model\Protocol\StatusCode;
use LightSaml\Model\XmlDSig\SignatureWriter;
use LightSaml\Model\XmlDSig\SignatureXmlReader;
use LightSaml\SamlConstants;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use Tests\BaseTestCase;

/**
 * validate() may be called several times on the same reader, e.g. when trying every candidate
 * key of an IdP. validateReference() detaches the signature node from the document on the first
 * call, which must not break the following ones.
 *
 * @see https://github.com/litesaml/lightsaml/issues/125
 */
class SignatureXmlReaderRepeatedValidationTest extends BaseTestCase
{
    public function test_validate_can_be_called_twice_with_the_same_key(): void
    {
        $sig = $this->deserializeAssertionSignature();
        $key = KeyHelper::createPublicKey($this->getSigningCertificate());

        $this->assertTrue($sig->validate($key));
        $this->assertTrue($sig->validate($key));
    }

    public function test_validate_succeeds_with_the_right_key_after_a_wrong_key(): void
    {
        $sig = $this->deserializeAssertionSignature();

        try {
            $sig->validate(KeyHelper::createPublicKey($this->getOtherCertificate()));
            $this->fail('Validation with the wrong key must fail');
        } catch (LightSamlSecurityException $e) {
            $this->assertSame('Unable to verify Signature', $e->getMessage());
        }

        $this->assertTrue($sig->validate(KeyHelper::createPublicKey($this->getSigningCertificate())));
    }

    public function test_validate_multi_finds_the_right_key_after_a_wrong_key(): void
    {
        $sig = $this->deserializeAssertionSignature();
        $wrong = new X509Credential($this->getOtherCertificate());
        $right = new X509Credential($this->getSigningCertificate());

        $this->assertSame($right, $sig->validateMulti([$wrong, $right]));
    }

    public function test_validate_still_rejects_wrong_key_after_a_successful_validation(): void
    {
        $sig = $this->deserializeAssertionSignature();

        $this->assertTrue($sig->validate(KeyHelper::createPublicKey($this->getSigningCertificate())));

        $this->expectException(LightSamlSecurityException::class);
        $this->expectExceptionMessage('Unable to verify Signature');

        $sig->validate(KeyHelper::createPublicKey($this->getOtherCertificate()));
    }

    private function deserializeAssertionSignature(): SignatureXmlReader
    {
        $response = (new Response())
            ->setID(Helper::generateID())
            ->setIssuer(new Issuer('https://idp.example.com'))
            ->setStatus(new Status(new StatusCode(SamlConstants::STATUS_SUCCESS)))
            ->addAssertion(
                (new Assertion())
                    ->setId(Helper::generateID())
                    ->setIssuer(new Issuer('https://idp.example.com'))
                    ->setSubject(
                        (new Subject())->setNameID(
                            new NameID('alice@example.com', SamlConstants::NAME_ID_FORMAT_EMAIL)
                        )
                    )
                    ->setSignature(new SignatureWriter($this->getSigningCertificate(), $this->getPrivateKey()))
            );

        $serCtx = new SerializationContext();
        $response->serialize($serCtx->getDocument(), $serCtx);

        $desCtx = new DeserializationContext();
        $desCtx->getDocument()->loadXML($serCtx->getDocument()->saveXML());
        $deserialized = new Response();
        $deserialized->deserialize($desCtx->getDocument(), $desCtx);

        /** @var SignatureXmlReader $sig */
        $sig = $deserialized->getAllAssertions()[0]->getSignature();

        return $sig;
    }

    private function getSigningCertificate(): X509Certificate
    {
        return X509Certificate::fromFile(__DIR__ . '/../../../resources/web_saml.crt');
    }

    private function getOtherCertificate(): X509Certificate
    {
        return X509Certificate::fromFile(__DIR__ . '/../../../resources/saml.crt');
    }

    private function getPrivateKey(): XMLSecurityKey
    {
        return KeyHelper::createPrivateKey(__DIR__ . '/../../../resources/web_saml.key', null, true);
    }
}
