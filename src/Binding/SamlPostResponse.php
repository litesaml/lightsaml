<?php

namespace LightSaml\Binding;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class SamlPostResponse implements ResponseInterface
{
    public function __construct(
        private ResponseInterface $inner,
        private readonly ?string $destination,
        private readonly array $data
    ) {
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getDestination(): ?string
    {
        return $this->destination;
    }

    public static function buildHtml(?string $destination, array $data): string
    {
        $content = <<<'EOT'
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>POST data</title>
</head>
<body onload="document.getElementById('a-very-unique-input-id#lightSAML').click();">

    <noscript>
        <p><strong>Note:</strong> Since your browser does not support JavaScript, you must press the button below once to proceed.</p>
    </noscript>

    <form method="post" action="%s">
        <input id="a-very-unique-input-id#lightSAML" type="submit" style="display:none;"/>

        %s

        <noscript>
            <input type="submit" value="Submit" />
        </noscript>

    </form>
</body>
</html>
EOT;
        $fields = '';
        foreach ($data as $name => $value) {
            $fields .= sprintf(
                '<input type="hidden" name="%s" value="%s" />',
                htmlspecialchars($name),
                htmlspecialchars($value)
            );
        }

        return sprintf($content, htmlspecialchars($destination ?? ''), $fields);
    }

    public function getStatusCode(): int
    {
        return $this->inner->getStatusCode();
    }

    public function withStatus(int $code, string $reasonPhrase = ''): static
    {
        $clone = clone $this;
        $clone->inner = $this->inner->withStatus($code, $reasonPhrase);

        return $clone;
    }

    public function getReasonPhrase(): string
    {
        return $this->inner->getReasonPhrase();
    }

    public function getProtocolVersion(): string
    {
        return $this->inner->getProtocolVersion();
    }

    public function withProtocolVersion(string $version): static
    {
        $clone = clone $this;
        $clone->inner = $this->inner->withProtocolVersion($version);

        return $clone;
    }

    public function getHeaders(): array
    {
        return $this->inner->getHeaders();
    }

    public function hasHeader(string $name): bool
    {
        return $this->inner->hasHeader($name);
    }

    public function getHeader(string $name): array
    {
        return $this->inner->getHeader($name);
    }

    public function getHeaderLine(string $name): string
    {
        return $this->inner->getHeaderLine($name);
    }

    public function withHeader(string $name, $value): static
    {
        $clone = clone $this;
        $clone->inner = $this->inner->withHeader($name, $value);

        return $clone;
    }

    public function withAddedHeader(string $name, $value): static
    {
        $clone = clone $this;
        $clone->inner = $this->inner->withAddedHeader($name, $value);

        return $clone;
    }

    public function withoutHeader(string $name): static
    {
        $clone = clone $this;
        $clone->inner = $this->inner->withoutHeader($name);

        return $clone;
    }

    public function getBody(): StreamInterface
    {
        return $this->inner->getBody();
    }

    public function withBody(StreamInterface $body): static
    {
        $clone = clone $this;
        $clone->inner = $this->inner->withBody($body);

        return $clone;
    }
}
