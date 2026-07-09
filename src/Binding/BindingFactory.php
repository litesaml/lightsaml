<?php

namespace LightSaml\Binding;

use LightSaml\Error\LightSamlBindingException;
use LightSaml\SamlConstants;
use LogicException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

class BindingFactory implements BindingFactoryInterface
{
    public function __construct(
        protected ?EventDispatcherInterface $eventDispatcher = null,
        protected ?ResponseFactoryInterface $responseFactory = null,
        protected ?StreamFactoryInterface $streamFactory = null,
    ) {
    }

    public function setEventDispatcher(?EventDispatcherInterface $eventDispatcher = null): static
    {
        $this->eventDispatcher = $eventDispatcher;

        return $this;
    }

    public function getBindingByRequest(ServerRequestInterface $request): AbstractBinding
    {
        $bindingType = $this->detectBindingType($request);

        if (null === $bindingType) {
            throw new LightSamlBindingException(sprintf(
                "Unable to detect binding type for '%s' request: invalid method or missing SAML parameters",
                $request->getMethod()
            ));
        }

        return $this->create($bindingType);
    }

    /**
     * @throws LogicException
     * @throws LightSamlBindingException
     */
    public function create(string $bindingType): AbstractBinding
    {
        $result = null;
        switch ($bindingType) {
            case SamlConstants::BINDING_SAML2_HTTP_REDIRECT:
                $result = new HttpRedirectBinding($this->responseFactory);
                break;

            case SamlConstants::BINDING_SAML2_HTTP_POST:
                $result = new HttpPostBinding($this->responseFactory, $this->streamFactory);
                break;

            case SamlConstants::BINDING_SAML2_HTTP_ARTIFACT:
                throw new LogicException('Artifact binding not implemented');
            case SamlConstants::BINDING_SAML2_SOAP:
                throw new LogicException('SOAP binding not implemented');
        }

        if ($result) {
            $result->setEventDispatcher($this->eventDispatcher);

            return $result;
        }

        throw new LightSamlBindingException(sprintf("Unknown binding type '%s'", $bindingType));
    }

    public function detectBindingType(ServerRequestInterface $request): ?string
    {
        $requestMethod = trim(strtoupper($request->getMethod()));
        if ('GET' === $requestMethod) {
            return $this->processGET($request);
        } elseif ('POST' === $requestMethod) {
            return $this->processPOST($request);
        }

        return null;
    }

    protected function processGET(ServerRequestInterface $request): ?string
    {
        $get = $request->getQueryParams();
        if (array_key_exists('SAMLRequest', $get) || array_key_exists('SAMLResponse', $get)) {
            return SamlConstants::BINDING_SAML2_HTTP_REDIRECT;
        } elseif (array_key_exists('SAMLart', $get)) {
            return SamlConstants::BINDING_SAML2_HTTP_ARTIFACT;
        }

        return null;
    }

    protected function processPOST(ServerRequestInterface $request): ?string
    {
        $post = (array) ($request->getParsedBody() ?? []);
        if (array_key_exists('SAMLRequest', $post) || array_key_exists('SAMLResponse', $post)) {
            return SamlConstants::BINDING_SAML2_HTTP_POST;
        } elseif (array_key_exists('SAMLart', $post)) {
            return SamlConstants::BINDING_SAML2_HTTP_ARTIFACT;
        } elseif (($contentType = $request->getHeaderLine('content-type')) !== '') {
            // Remove charset
            if (false !== $pos = strpos($contentType, ';')) {
                $contentType = substr($contentType, 0, $pos);
            }
            if ('text/xml' === trim($contentType)) {
                return SamlConstants::BINDING_SAML2_SOAP;
            }
        }

        return null;
    }
}
