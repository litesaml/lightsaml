<?php

namespace Tests\Meta;

use LightSaml\Credential\X509Certificate;
use LightSaml\Meta\ParameterBag;
use LightSaml\Meta\SigningOptions;
use Tests\BaseTestCase;

class SigningOptionsTest extends BaseTestCase
{
    public function test_constructs_wout_arguments(): void
    {
        new SigningOptions();
        $this->assertTrue(true);
    }

    public function test_constructs_with_xml_key_and_certificate(): void
    {
        new SigningOptions($this->getXmlSecurityKeyMock(), new X509Certificate());
        $this->assertTrue(true);
    }

    public function test_enabled_by_default(): void
    {
        $options = new SigningOptions();
        $this->assertTrue($options->isEnabled());
    }

    public function test_can_be_disabled(): void
    {
        $options = new SigningOptions();
        $options->setEnabled(false);
        $this->assertFalse($options->isEnabled());
    }

    public function test_returns_certificate_constructed_with(): void
    {
        $options = new SigningOptions($key = $this->getXmlSecurityKeyMock(), $certificate = new X509Certificate());
        $this->assertSame($certificate, $options->getCertificate());
    }

    public function test_returns_xml_key_constructed_with(): void
    {
        $options = new SigningOptions($key = $this->getXmlSecurityKeyMock(), $certificate = new X509Certificate());
        $this->assertSame($key, $options->getPrivateKey());
    }

    public function test_returns_set_certificate(): void
    {
        $options = new SigningOptions();
        $options->setCertificate($certificate = new X509Certificate());
        $this->assertSame($certificate, $options->getCertificate());
    }

    public function test_returns_set_xml_key(): void
    {
        $options = new SigningOptions();
        $options->setPrivateKey($key = $this->getXmlSecurityKeyMock());
        $this->assertSame($key, $options->getPrivateKey());
    }

    public function test_returns_certificate_options(): void
    {
        $options = new SigningOptions();
        $this->assertNotNull($options->getCertificateOptions());
        $this->assertInstanceOf(ParameterBag::class, $options->getCertificateOptions());
    }
}
