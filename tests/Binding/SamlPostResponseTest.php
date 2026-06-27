<?php

namespace Tests\Binding;

use LightSaml\Binding\SamlPostResponse;
use Nyholm\Psr7\Factory\Psr17Factory;
use Tests\BaseTestCase;

class SamlPostResponseTest extends BaseTestCase
{
    private Psr17Factory $factory;

    protected function setUp(): void
    {
        $this->factory = new Psr17Factory();
    }

    /** @param array<string, string> $data */
    private function makeResponse(?string $destination = 'https://sp.example.com/acs', array $data = ['SAMLResponse' => 'abc']): SamlPostResponse
    {
        $inner = $this->factory->createResponse(200);
        return new SamlPostResponse($inner, $destination, $data);
    }

    public function test_get_destination_returns_set_value(): void
    {
        $response = $this->makeResponse('https://sp.example.com/acs');
        $this->assertSame('https://sp.example.com/acs', $response->getDestination());
    }

    public function test_get_destination_returns_null_when_null(): void
    {
        $response = $this->makeResponse(null);
        $this->assertNull($response->getDestination());
    }

    public function test_get_data_returns_set_value(): void
    {
        $data = ['SAMLResponse' => 'encoded_xml', 'RelayState' => 'state'];
        $response = $this->makeResponse('https://sp.example.com/acs', $data);
        $this->assertSame($data, $response->getData());
    }

    public function test_get_status_code_delegates_to_inner(): void
    {
        $inner = $this->factory->createResponse(201);
        $response = new SamlPostResponse($inner, 'https://sp.example.com/acs', []);
        $this->assertSame(201, $response->getStatusCode());
    }

    public function test_get_reason_phrase_delegates_to_inner(): void
    {
        $inner = $this->factory->createResponse(200, 'OK');
        $response = new SamlPostResponse($inner, 'https://sp.example.com/acs', []);
        $this->assertSame('OK', $response->getReasonPhrase());
    }

    public function test_get_protocol_version_delegates_to_inner(): void
    {
        $inner = $this->factory->createResponse(200);
        $response = new SamlPostResponse($inner, 'https://sp.example.com/acs', []);
        $this->assertSame('1.1', $response->getProtocolVersion());
    }

    public function test_with_status_returns_new_instance_and_preserves_metadata(): void
    {
        $response = $this->makeResponse('https://sp.example.com/acs', ['SAMLResponse' => 'abc']);
        $new = $response->withStatus(302);

        $this->assertNotSame($response, $new);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(302, $new->getStatusCode());
        $this->assertSame('https://sp.example.com/acs', $new->getDestination());
        $this->assertSame(['SAMLResponse' => 'abc'], $new->getData());
    }

    public function test_with_header_returns_new_instance_and_preserves_metadata(): void
    {
        $response = $this->makeResponse('https://sp.example.com/acs', ['SAMLResponse' => 'abc']);
        $new = $response->withHeader('Content-Type', 'text/html');

        $this->assertNotSame($response, $new);
        $this->assertFalse($response->hasHeader('Content-Type'));
        $this->assertTrue($new->hasHeader('Content-Type'));
        $this->assertSame('text/html', $new->getHeaderLine('Content-Type'));
        $this->assertSame('https://sp.example.com/acs', $new->getDestination());
        $this->assertSame(['SAMLResponse' => 'abc'], $new->getData());
    }

    public function test_with_added_header_returns_new_instance(): void
    {
        $response = $this->makeResponse()->withHeader('X-Foo', 'a');
        $new = $response->withAddedHeader('X-Foo', 'b');

        $this->assertNotSame($response, $new);
        $this->assertSame(['a'], $response->getHeader('X-Foo'));
        $this->assertSame(['a', 'b'], $new->getHeader('X-Foo'));
    }

    public function test_without_header_returns_new_instance(): void
    {
        $response = $this->makeResponse()->withHeader('X-Foo', 'bar');
        $new = $response->withoutHeader('X-Foo');

        $this->assertNotSame($response, $new);
        $this->assertTrue($response->hasHeader('X-Foo'));
        $this->assertFalse($new->hasHeader('X-Foo'));
    }

    public function test_with_body_returns_new_instance(): void
    {
        $response = $this->makeResponse();
        $stream = $this->factory->createStream('hello');
        $new = $response->withBody($stream);

        $this->assertNotSame($response, $new);
        $this->assertSame('hello', (string) $new->getBody());
    }

    public function test_with_protocol_version_returns_new_instance(): void
    {
        $response = $this->makeResponse();
        $new = $response->withProtocolVersion('2.0');

        $this->assertNotSame($response, $new);
        $this->assertSame('1.1', $response->getProtocolVersion());
        $this->assertSame('2.0', $new->getProtocolVersion());
    }

    public function test_get_headers_delegates_to_inner(): void
    {
        $response = $this->makeResponse()->withHeader('X-Custom', 'value');
        $headers = $response->getHeaders();
        $this->assertArrayHasKey('X-Custom', $headers);
    }

    public function test_build_html_contains_destination_and_fields(): void
    {
        $html = SamlPostResponse::buildHtml('https://sp.example.com/acs', ['SAMLResponse' => 'encoded<>&"']);

        $this->assertStringContainsString('action="https://sp.example.com/acs"', $html);
        $this->assertStringContainsString('name="SAMLResponse"', $html);
        $this->assertStringContainsString('value="encoded&lt;&gt;&amp;&quot;"', $html);
    }

    public function test_build_html_handles_null_destination(): void
    {
        $html = SamlPostResponse::buildHtml(null, []);
        $this->assertStringContainsString('action=""', $html);
    }

    public function test_with_status_chained_multiple_times_preserves_immutability(): void
    {
        $r1 = $this->makeResponse();
        $r2 = $r1->withStatus(301);
        $r3 = $r2->withStatus(302);

        $this->assertSame(200, $r1->getStatusCode());
        $this->assertSame(301, $r2->getStatusCode());
        $this->assertSame(302, $r3->getStatusCode());
    }
}
