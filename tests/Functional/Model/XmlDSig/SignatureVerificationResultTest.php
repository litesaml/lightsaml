<?php

namespace Tests\Functional\Model\XmlDSig;

use DOMDocument;
use DOMXPath;
use LightSaml\Context\Model\DeserializationContext;
use LightSaml\Context\Model\SerializationContext;
use LightSaml\Credential\KeyHelper;
use LightSaml\Credential\X509Certificate;
use LightSaml\Error\LightSamlSecurityException;
use LightSaml\Helper;
use LightSaml\Model\Assertion\Assertion;
use LightSaml\Model\Assertion\Issuer;
use LightSaml\Model\Assertion\NameID;
use LightSaml\Model\Assertion\Subject;
use LightSaml\Model\Protocol\Response;
use LightSaml\Model\Protocol\Status;
use LightSaml\Model\Protocol\StatusCode;
use LightSaml\Model\XmlDSig\SignatureStringReader;
use LightSaml\Model\XmlDSig\SignatureWriter;
use LightSaml\Model\XmlDSig\SignatureXmlReader;
use LightSaml\SamlConstants;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use Tests\BaseTestCase;

/**
 * xmlseclibs reports signature verification as 1 (valid), 0 (invalid) or -1 (error).
 * An error must never be treated as a valid signature.
 */
class SignatureVerificationResultTest extends BaseTestCase
{
    public function test_xml_signature_with_valid_signature_value_is_accepted(): void
    {
        $sig = $this->deserializeAssertionSignature($this->buildSignedResponseXml());

        $this->assertTrue($sig->validate(KeyHelper::createPublicKey($this->getCertificate())));
    }

    public function test_xml_signature_with_invalid_signature_value_is_rejected(): void
    {
        $xml = $this->replaceSignatureValue($this->buildSignedResponseXml(), base64_encode(str_repeat("\0", 256)));
        $sig = $this->deserializeAssertionSignature($xml);

        $this->expectException(LightSamlSecurityException::class);
        $this->expectExceptionMessage('Unable to verify Signature');

        $sig->validate(KeyHelper::createPublicKey($this->getCertificate()));
    }

    public function test_xml_signature_with_non_base64_signature_value_is_rejected(): void
    {
        $xml = $this->replaceSignatureValue($this->buildSignedResponseXml(), 'not!base64!!');
        $sig = $this->deserializeAssertionSignature($xml);

        $this->expectException(LightSamlSecurityException::class);
        $this->expectExceptionMessage('Unable to verify Signature');

        $sig->validate(KeyHelper::createPublicKey($this->getCertificate()));
    }

    public function test_string_signature_verification_error_is_rejected(): void
    {
        $key = $this->createMock(XMLSecurityKey::class);
        $key->type = XMLSecurityKey::RSA_SHA256;
        $key->method('verifySignature')->willReturn(-1);

        $reader = new SignatureStringReader(base64_encode('signature'), XMLSecurityKey::RSA_SHA256, 'data');

        $this->expectException(LightSamlSecurityException::class);
        $this->expectExceptionMessage('Unable to validate signature on query string');

        $reader->validate($key);
    }

    private function buildSignedResponseXml(): string
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
                    ->setSignature(new SignatureWriter($this->getCertificate(), $this->getPrivateKey()))
            );

        $serCtx = new SerializationContext();
        $response->serialize($serCtx->getDocument(), $serCtx);

        return $serCtx->getDocument()->saveXML();
    }

    private function replaceSignatureValue(string $xml, string $signatureValue): string
    {
        $doc = new DOMDocument();
        $doc->loadXML($xml);
        $xp = new DOMXPath($doc);
        $xp->registerNamespace('ds', SamlConstants::NS_XMLDSIG);
        $xp->query('//ds:SignatureValue')->item(0)->textContent = $signatureValue;

        return $doc->saveXML();
    }

    private function deserializeAssertionSignature(string $xml): SignatureXmlReader
    {
        $desCtx = new DeserializationContext();
        $desCtx->getDocument()->loadXML($xml);
        $response = new Response();
        $response->deserialize($desCtx->getDocument(), $desCtx);

        /** @var SignatureXmlReader $sig */
        $sig = $response->getAllAssertions()[0]->getSignature();

        return $sig;
    }

    private function getCertificate(): X509Certificate
    {
        return X509Certificate::fromFile(__DIR__ . '/../../../resources/web_saml.crt');
    }

    private function getPrivateKey(): XMLSecurityKey
    {
        return KeyHelper::createPrivateKey(__DIR__ . '/../../../resources/web_saml.key', null, true);
    }
}
