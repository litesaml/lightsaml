<?php

namespace Tests\Functional\Model\Protocol;

use DOMDocument;
use DOMElement;
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
use LightSaml\Model\XmlDSig\SignatureWriter;
use LightSaml\Model\XmlDSig\SignatureXmlReader;
use LightSaml\SamlConstants;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use Tests\BaseTestCase;

/**
 * Regression tests for XML Signature Wrapping (XSW) attacks.
 *
 * An attacker who captures a genuine signed assertion can bury it (without its ds:Signature)
 * inside samlp:Extensions, making it the first @ID element in document order. xmlseclibs then
 * resolves the Reference URI to this element and validates the digest against it — successfully.
 * A forged direct-child assertion carrying the same ID, a verbatim copy of the genuine signature,
 * and attacker-controlled claims is what LightSAML actually collects and reads.
 *
 * The fix rejects this by verifying (a) the ds:Signature's parent element carries the referenced ID
 * and (b) that ID is unique in the document. Both checks run before validateReference() detaches
 * the signature node.
 */
class XmlSignatureWrappingTest extends BaseTestCase
{
    public function test_valid_signed_assertion_is_still_accepted(): void
    {
        $assertionId = Helper::generateID();
        $response = $this->buildSignedResponse($assertionId, 'alice@example.com');

        $serCtx = new SerializationContext();
        $response->serialize($serCtx->getDocument(), $serCtx);
        $xml = $serCtx->getDocument()->saveXML();

        $desCtx = new DeserializationContext();
        $desCtx->getDocument()->loadXML($xml);
        $deserialized = new Response();
        $deserialized->deserialize($desCtx->getDocument(), $desCtx);

        /** @var SignatureXmlReader $sig */
        $sig = $deserialized->getAllAssertions()[0]->getSignature();
        $this->assertTrue($sig->validate(KeyHelper::createPublicKey($this->getCertificate())));
    }

    public function test_rejects_xsw_duplicate_id_in_document(): void
    {
        $assertionId = Helper::generateID();
        $response = $this->buildSignedResponse($assertionId, 'alice@example.com');

        $serCtx = new SerializationContext();
        $response->serialize($serCtx->getDocument(), $serCtx);
        $genuineXml = $serCtx->getDocument()->saveXML();

        $tamperedXml = $this->buildXswPayload($genuineXml, $assertionId);

        $desCtx = new DeserializationContext();
        $desCtx->getDocument()->loadXML($tamperedXml);
        $tamperedResponse = new Response();
        $tamperedResponse->deserialize($desCtx->getDocument(), $desCtx);

        $assertions = $tamperedResponse->getAllAssertions();
        $this->assertCount(1, $assertions, 'LightSAML should collect only the forged direct-child assertion');

        // Confirm the collected assertion carries the attacker claims (pre-condition for the bypass)
        $nameId = $assertions[0]->getSubject()->getNameID()->getValue();
        $this->assertSame('attacker@evil.com', $nameId, 'Collected assertion must carry attacker NameID');

        // Signature validation must reject the tampered document
        $this->expectException(LightSamlSecurityException::class);

        /** @var SignatureXmlReader $sig */
        $sig = $assertions[0]->getSignature();
        $sig->validate(KeyHelper::createPublicKey($this->getCertificate()));
    }

    public function test_rejects_xsw_reference_uri_mismatch(): void
    {
        // Build a response, then move the signature into a different element (no ID match).
        $assertionId = Helper::generateID();
        $response = $this->buildSignedResponse($assertionId, 'alice@example.com');

        $serCtx = new SerializationContext();
        $response->serialize($serCtx->getDocument(), $serCtx);
        $xml = $serCtx->getDocument()->saveXML();

        $doc = new DOMDocument();
        $doc->loadXML($xml);
        $xp = new DOMXPath($doc);
        $xp->registerNamespace('saml', SamlConstants::NS_ASSERTION);
        $xp->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');

        // Move the ds:Signature to a wrapper element that has a *different* ID
        $assertion = $xp->query('//saml:Assertion[@ID="' . $assertionId . '"]')->item(0);
        $sig = $xp->query('./ds:Signature', $assertion)->item(0);

        $wrapper = $doc->createElementNS(SamlConstants::NS_ASSERTION, 'saml:Assertion');
        $wrapper->setAttribute('ID', '_different_id');
        $wrapper->setAttribute('Version', '2.0');
        $wrapper->setAttribute('IssueInstant', '2024-01-01T00:00:00Z');
        $wrapper->appendChild($sig);
        $assertion->parentNode->appendChild($wrapper);

        $desCtx = new DeserializationContext();
        $desCtx->getDocument()->loadXML($doc->saveXML());
        $deserialized = new Response();
        $deserialized->deserialize($desCtx->getDocument(), $desCtx);

        // Find the assertion that still has a signature (the wrapper)
        $sigAssertion = null;
        foreach ($deserialized->getAllAssertions() as $a) {
            if ($a->getSignature() instanceof SignatureXmlReader) {
                $sigAssertion = $a;
            }
        }
        $this->assertNotNull($sigAssertion);

        $this->expectException(LightSamlSecurityException::class);

        /** @var SignatureXmlReader $reader */
        $reader = $sigAssertion->getSignature();
        $reader->validate(KeyHelper::createPublicKey($this->getCertificate()));
    }

    private function buildSignedResponse(string $assertionId, string $nameIdValue): Response
    {
        return (new Response())
            ->setID(Helper::generateID())
            ->setIssuer(new Issuer('https://idp.example.com'))
            ->setStatus(new Status(new StatusCode(SamlConstants::STATUS_SUCCESS)))
            ->addAssertion(
                (new Assertion())
                    ->setId($assertionId)
                    ->setIssuer(new Issuer('https://idp.example.com'))
                    ->setSubject(
                        (new Subject())->setNameID(
                            new NameID($nameIdValue, SamlConstants::NAME_ID_FORMAT_EMAIL)
                        )
                    )
                    ->setSignature(new SignatureWriter($this->getCertificate(), $this->getPrivateKey()))
            );
    }

    /**
     * Transforms a genuine signed response into an XSW payload:
     * - Strips ds:Signature from the genuine assertion and buries it in samlp:Extensions
     *   (making it the first @ID element in document order, which xmlseclibs resolves to)
     * - Injects a forged direct-child assertion with the same ID, the copied ds:Signature,
     *   and attacker-controlled NameID
     */
    private function buildXswPayload(string $genuineXml, string $assertionId): string
    {
        $doc = new DOMDocument();
        $doc->loadXML($genuineXml);

        $xp = new DOMXPath($doc);
        $xp->registerNamespace('samlp', SamlConstants::NS_PROTOCOL);
        $xp->registerNamespace('saml', SamlConstants::NS_ASSERTION);
        $xp->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');

        /** @var DOMElement $resp */
        $resp = $doc->documentElement;

        /** @var DOMElement $genuineAssertion */
        $genuineAssertion = $xp->query('//saml:Assertion[@ID="' . $assertionId . '"]')->item(0);

        // Copy the signature verbatim (to be pasted into the forged assertion)
        $copiedSig = $xp->query('./ds:Signature', $genuineAssertion)->item(0)->cloneNode(true);

        // Build the buried element: genuine assertion content without its ds:Signature
        $buried = $genuineAssertion->cloneNode(true);
        $buried->removeChild($xp->query('./ds:Signature', $buried)->item(0));

        // samlp:Extensions wraps the buried genuine assertion — it will be first in document order
        $ext = $doc->createElementNS(SamlConstants::NS_PROTOCOL, 'samlp:Extensions');
        $ext->appendChild($buried);
        $resp->replaceChild($ext, $genuineAssertion);

        // Forged assertion: same ID, copied signature, attacker NameID
        $forged = $doc->createElementNS(SamlConstants::NS_ASSERTION, 'saml:Assertion');
        $forged->setAttribute('ID', $assertionId);
        $forged->setAttribute('Version', '2.0');
        $forged->setAttribute('IssueInstant', '2024-01-01T00:00:00Z');

        $issuer = $doc->createElementNS(SamlConstants::NS_ASSERTION, 'saml:Issuer');
        $issuer->textContent = 'https://idp.example.com';
        $forged->appendChild($issuer);

        $forged->appendChild($copiedSig);

        $subject = $doc->createElementNS(SamlConstants::NS_ASSERTION, 'saml:Subject');
        $nameId = $doc->createElementNS(SamlConstants::NS_ASSERTION, 'saml:NameID');
        $nameId->setAttribute('Format', SamlConstants::NAME_ID_FORMAT_EMAIL);
        $nameId->textContent = 'attacker@evil.com';
        $subject->appendChild($nameId);
        $forged->appendChild($subject);

        $resp->appendChild($forged);

        return $doc->saveXML();
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
