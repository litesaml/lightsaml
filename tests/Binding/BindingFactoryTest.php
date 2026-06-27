<?php

namespace Tests\Binding;

use LightSaml\Binding\BindingFactory;
use LightSaml\Binding\HttpPostBinding;
use LightSaml\Binding\HttpRedirectBinding;
use LightSaml\Error\LightSamlBindingException;
use LightSaml\SamlConstants;
use LogicException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tests\BaseTestCase;

class BindingFactoryTest extends BaseTestCase
{
    public function test__create_http_redirect(): void
    {
        $factory = new BindingFactory();
        $binding = $factory->create(SamlConstants::BINDING_SAML2_HTTP_REDIRECT);
        $this->assertInstanceOf(HttpRedirectBinding::class, $binding);
    }

    public function test__create_http_post(): void
    {
        $factory = new BindingFactory();
        $binding = $factory->create(SamlConstants::BINDING_SAML2_HTTP_POST);
        $this->assertInstanceOf(HttpPostBinding::class, $binding);
    }

    public function test__create_throws_not_implemented_error_for_soap(): void
    {
        $this->expectExceptionMessage("SOAP binding not implemented");
        $this->expectException(LogicException::class);
        $factory = new BindingFactory();
        $factory->create(SamlConstants::BINDING_SAML2_SOAP);
    }

    public function test__create_throws_not_implemented_error_for_artifact(): void
    {
        $this->expectExceptionMessage("Artifact binding not implemented");
        $this->expectException(LogicException::class);
        $factory = new BindingFactory();
        $factory->create(SamlConstants::BINDING_SAML2_HTTP_ARTIFACT);
    }

    public function test__create_throws_for_unknown_binding(): void
    {
        $this->expectExceptionMessage("Unknown binding type 'foo'");
        $this->expectException(LightSamlBindingException::class);
        $factory = new BindingFactory();
        $factory->create('foo');
    }

    public function test__detect_http_redirect(): void
    {
        $request = $this->createHttpRedirectRequest();

        $factory = new BindingFactory();

        $this->assertEquals(SamlConstants::BINDING_SAML2_HTTP_REDIRECT, $factory->detectBindingType($request));
    }

    public function test__detect_http_post(): void
    {
        $request = $this->createHttpPostRequest();

        $factory = new BindingFactory();

        $this->assertEquals(SamlConstants::BINDING_SAML2_HTTP_POST, $factory->detectBindingType($request));
    }

    public function test__detect_artifact_post(): void
    {
        $request = $this->createArtifactPostRequest();

        $factory = new BindingFactory();

        $this->assertEquals(SamlConstants::BINDING_SAML2_HTTP_ARTIFACT, $factory->detectBindingType($request));
    }

    public function test__detect_artifact_get(): void
    {
        $request = $this->createArtifactGetRequest();

        $factory = new BindingFactory();

        $this->assertEquals(SamlConstants::BINDING_SAML2_HTTP_ARTIFACT, $factory->detectBindingType($request));
    }

    public function test__detect_soap(): void
    {
        $request = $this->createSoapRequest();

        $factory = new BindingFactory();

        $this->assertEquals(SamlConstants::BINDING_SAML2_SOAP, $factory->detectBindingType($request));
    }

    public function test__detect_none_get(): void
    {
        $factory = new Psr17Factory();
        $request = $factory->createServerRequest('GET', '/');

        $bindingFactory = new BindingFactory();

        $this->assertNull($bindingFactory->detectBindingType($request));
    }

    public function test__detect_none_post(): void
    {
        $factory = new Psr17Factory();
        $request = $factory->createServerRequest('POST', '/');

        $bindingFactory = new BindingFactory();

        $this->assertNull($bindingFactory->detectBindingType($request));
    }

    public function test__get_binding_by_request_http_redirect(): void
    {
        $request = $this->createHttpRedirectRequest();
        $factory = new BindingFactory();
        $this->assertInstanceOf(HttpRedirectBinding::class, $factory->getBindingByRequest($request));
    }

    public function test__get_binding_by_request_http_post(): void
    {
        $request = $this->createHttpPostRequest();
        $factory = new BindingFactory();
        $this->assertInstanceOf(HttpPostBinding::class, $factory->getBindingByRequest($request));
    }

    public function test__create_with_event_dispatcher(): void
    {
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $factory = new BindingFactory($eventDispatcher);
        $binding = $factory->create(SamlConstants::BINDING_SAML2_HTTP_REDIRECT);
        $this->assertInstanceOf(HttpRedirectBinding::class, $binding);
        $this->assertEquals($eventDispatcher, $binding->getEventDispatcher());
    }

    private function createHttpPostRequest(): ServerRequestInterface
    {
        $factory = new Psr17Factory();
        return $factory->createServerRequest('POST', '/')->withParsedBody(['SAMLRequest' => 'request']);
    }

    private function createHttpRedirectRequest(): ServerRequestInterface
    {
        $factory = new Psr17Factory();
        return $factory->createServerRequest('GET', '/')->withQueryParams(['SAMLRequest' => 'request']);
    }

    private function createArtifactPostRequest(): ServerRequestInterface
    {
        $factory = new Psr17Factory();
        return $factory->createServerRequest('POST', '/')->withParsedBody(['SAMLart' => 'request']);
    }

    private function createArtifactGetRequest(): ServerRequestInterface
    {
        $factory = new Psr17Factory();
        return $factory->createServerRequest('GET', '/')->withQueryParams(['SAMLart' => 'request']);
    }

    private function createSoapRequest(): ServerRequestInterface
    {
        $factory = new Psr17Factory();
        $request = $factory->createServerRequest('POST', '/')->withHeader('Content-Type', 'text/xml; charset=utf-8');
        assert($request instanceof ServerRequestInterface);

        return $request;
    }
}
