<?php

namespace LightSaml\Tests\Binding;

use LightSaml\Binding\BindingFactory;
use LightSaml\SamlConstants;
use LightSaml\Tests\BaseTestCase;
use Psr\Http\Message\ServerRequestInterface;

class BindingFactoryTest extends BaseTestCase
{
    public function test__create_http_redirect()
    {
        $factory = new BindingFactory();
        $binding = $factory->create(SamlConstants::BINDING_SAML2_HTTP_REDIRECT);
        $this->assertInstanceOf('LightSaml\Binding\HttpRedirectBinding', $binding);
    }

    public function test__create_http_post()
    {
        $factory = new BindingFactory();
        $binding = $factory->create(SamlConstants::BINDING_SAML2_HTTP_POST);
        $this->assertInstanceOf('LightSaml\Binding\HttpPostBinding', $binding);
    }

    public function test__create_throws_not_implemented_error_for_soap()
    {
        $this->expectExceptionMessage("SOAP binding not implemented");
        $this->expectException(\LogicException::class);
        $factory = new BindingFactory();
        $factory->create(SamlConstants::BINDING_SAML2_SOAP);
    }

    public function test__create_throws_not_implemented_error_for_artifact()
    {
        $this->expectExceptionMessage("Artifact binding not implemented");
        $this->expectException(\LogicException::class);
        $factory = new BindingFactory();
        $factory->create(SamlConstants::BINDING_SAML2_HTTP_ARTIFACT);
    }

    public function test__create_throws_for_unknown_binding()
    {
        $this->expectExceptionMessage("Unknown binding type 'foo'");
        $this->expectException(\LightSaml\Error\LightSamlBindingException::class);
        $factory = new BindingFactory();
        $factory->create('foo');
    }

    public function test__detect_http_redirect()
    {
        $request = $this->getRequestMock('GET', ['SAMLRequest' => 'request']);

        $factory = new BindingFactory();

        $this->assertEquals(SamlConstants::BINDING_SAML2_HTTP_REDIRECT, $factory->detectBindingType($request));
    }

    public function test__detect_http_post()
    {
        $request = $this->getRequestMock('POST', ['SAMLRequest' => 'request']);

        $factory = new BindingFactory();

        $this->assertEquals(SamlConstants::BINDING_SAML2_HTTP_POST, $factory->detectBindingType($request));
    }

    public function test__detect_artifact_post()
    {
        $request = $this->getRequestMock('POST', ['SAMLart' => 'request']);

        $factory = new BindingFactory();

        $this->assertEquals(SamlConstants::BINDING_SAML2_HTTP_ARTIFACT, $factory->detectBindingType($request));
    }

    public function test__detect_artifact_get()
    {
        $request = $this->getRequestMock('GET', ['SAMLart' => 'request']);

        $factory = new BindingFactory();

        $this->assertEquals(SamlConstants::BINDING_SAML2_HTTP_ARTIFACT, $factory->detectBindingType($request));
    }

    public function test__detect_soap()
    {
        $request = $this->getRequestMock('POST', [], ['CONTENT_TYPE' => 'text/xml; charset=utf-8']);

        $factory = new BindingFactory();

        $this->assertEquals(SamlConstants::BINDING_SAML2_SOAP, $factory->detectBindingType($request));
    }

    public function test__detect_none_get()
    {
        $request = $this->getRequestMock('GET');

        $factory = new BindingFactory();

        $this->assertNull($factory->detectBindingType($request));
    }

    public function test__detect_none_post()
    {
        $request = $this->getRequestMock('POST');

        $factory = new BindingFactory();

        $this->assertNull($factory->detectBindingType($request));
    }

    public function test__get_binding_by_request_http_redirect()
    {
        $request = $this->getRequestMock('GET', ['SAMLRequest' => 'request']);
        $factory = new BindingFactory();
        $this->assertInstanceOf('LightSaml\Binding\HttpRedirectBinding', $factory->getBindingByRequest($request));
    }

    public function test__get_binding_by_request_http_post()
    {
        $request = $this->getRequestMock('POST', ['SAMLRequest' => 'request']);
        $factory = new BindingFactory();
        $this->assertInstanceOf('LightSaml\Binding\HttpPostBinding', $factory->getBindingByRequest($request));
    }
}
