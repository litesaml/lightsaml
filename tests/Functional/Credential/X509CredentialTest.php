<?php

namespace Tests\Functional\Credential;

use LightSaml\Credential\KeyHelper;
use LightSaml\Credential\X509Certificate;
use LightSaml\Credential\X509Credential;
use Tests\BaseTestCase;

class X509CredentialTest extends BaseTestCase
{
    public function test_public_key(): void
    {
        $certificate = new X509Certificate();
        $certificate->loadFromFile(__DIR__ . '/../../resources/saml.crt');

        $credential = new X509Credential($certificate);

        $this->assertSame($certificate, $credential->getCertificate());
        $this->assertNotNull($credential->getPublicKey());
        // xmlseclibs 4 re-serialises the certificate PEM (line wrapping / endings), so compare
        // the decoded certificate bytes rather than the exact PEM string.
        $this->assertEquals(
            $certificate->getData(),
            (new X509Certificate())->loadPem($credential->getPublicKey()->getX509Certificate())->getData()
        );

        $this->assertNull($credential->getPrivateKey());

        $this->assertEquals(['/C=RS/ST=Serbia/O=BOS/CN=mt.evo.team'], $credential->getKeyNames());
    }

    public function test_private_key(): void
    {
        $certificate = new X509Certificate();
        $certificate->loadFromFile(__DIR__ . '/../../resources/saml.crt');

        $privateKey = KeyHelper::createPrivateKey(__DIR__ . '/../../resources/saml.pem', null, true);

        $credential = new X509Credential($certificate, $privateKey);

        $this->assertSame($certificate, $credential->getCertificate());
        $this->assertNotNull($credential->getPublicKey());
        // xmlseclibs 4 re-serialises the certificate PEM (line wrapping / endings), so compare
        // the decoded certificate bytes rather than the exact PEM string.
        $this->assertEquals(
            $certificate->getData(),
            (new X509Certificate())->loadPem($credential->getPublicKey()->getX509Certificate())->getData()
        );

        $this->assertNotNull($credential->getPrivateKey());
    }
}
