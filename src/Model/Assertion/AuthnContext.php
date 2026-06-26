<?php

namespace LightSaml\Model\Assertion;

use DOMNode;
use LightSaml\Model\AbstractSamlModel;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LightSaml\SamlConstants;

class AuthnContext extends AbstractSamlModel
{
    /**
     * @var string|null
     */
    protected $authnContextClassRef;

    /**
     * @var string|null
     */
    protected $authnContextDecl;

    /**
     * @var string|null
     */
    protected $authnContextDeclRef;

    /**
     * @var string|null
     */
    protected $authenticatingAuthority;

    public function setAuthenticatingAuthority(?string $authenticatingAuthority): static
    {
        $this->authenticatingAuthority = (string) $authenticatingAuthority;

        return $this;
    }

    public function getAuthenticatingAuthority(): ?string
    {
        return $this->authenticatingAuthority;
    }

    public function setAuthnContextClassRef(?string $authnContextClassRef): static
    {
        $this->authnContextClassRef = (string) $authnContextClassRef;

        return $this;
    }

    public function getAuthnContextClassRef(): ?string
    {
        return $this->authnContextClassRef;
    }

    public function setAuthnContextDecl(?string $authnContextDecl): static
    {
        $this->authnContextDecl = (string) $authnContextDecl;

        return $this;
    }

    public function getAuthnContextDecl(): ?string
    {
        return $this->authnContextDecl;
    }

    public function setAuthnContextDeclRef(?string $authnContextDeclRef): static
    {
        $this->authnContextDeclRef = (string) $authnContextDeclRef;

        return $this;
    }

    public function getAuthnContextDeclRef(): ?string
    {
        return $this->authnContextDeclRef;
    }

    public function serialize(DOMNode $parent, SerializationContext $context): void
    {
        $result = $this->createElement('AuthnContext', SamlConstants::NS_ASSERTION, $parent, $context);

        $this->singleElementsToXml(
            ['AuthnContextClassRef', 'AuthnContextDecl', 'AuthnContextDeclRef', 'AuthenticatingAuthority'],
            $result,
            $context,
            SamlConstants::NS_ASSERTION
        );
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'AuthnContext', SamlConstants::NS_ASSERTION);

        $this->singleElementsFromXml($node, $context, [
            'AuthnContextClassRef' => ['saml', null],
            'AuthnContextDecl' => ['saml', null],
            'AuthnContextDeclRef' => ['saml', null],
            'AuthenticatingAuthority' => ['saml', null],
        ]);
    }
}
