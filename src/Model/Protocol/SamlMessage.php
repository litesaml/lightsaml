<?php

namespace LightSaml\Model\Protocol;

use DateTime;
use DOMComment;
use DOMNode;
use Exception;
use LightSaml\Context\Model\DeserializationContext;
use LightSaml\Context\Model\SerializationContext;
use LightSaml\Error\LightSamlXmlException;
use LightSaml\Helper;
use LightSaml\Model\AbstractSamlModel;
use LightSaml\Model\Assertion\Issuer;
use LightSaml\Model\XmlDSig\Signature;
use LightSaml\Model\XmlDSig\SignatureXmlReader;
use LightSaml\SamlConstants;
use LogicException;

abstract class SamlMessage extends AbstractSamlModel
{
    protected ?string $id = null;

    protected string $version = SamlConstants::VERSION_20;

    protected ?int $issueInstant = null;

    protected ?string $destination = null;

    protected ?Issuer $issuer = null;

    protected ?string $consent = null;

    protected ?Signature $signature = null;

    protected ?string $relayState = null;

    /**
     *
     *
     * @throws Exception
     */
    public static function fromXML(string $xml, DeserializationContext $context): AuthnRequest|LogoutRequest|LogoutResponse|Response|SamlMessage
    {
        $context->getDocument()->loadXML($xml);

        $node = $context->getDocument()->firstChild;
        while ($node && $node instanceof DOMComment) {
            $node = $node->nextSibling;
        }
        if (!$node instanceof DOMNode) {
            throw new LightSamlXmlException('Empty XML');
        }

        if (SamlConstants::NS_PROTOCOL !== $node->namespaceURI) {
            throw new LightSamlXmlException(sprintf("Invalid namespace '%s' of the root XML element, expected '%s'", $context->getDocument()->namespaceURI, SamlConstants::NS_PROTOCOL));
        }

        $map = [
            'AttributeQuery' => null,
            'AuthnRequest' => AuthnRequest::class,
            'LogoutResponse' => LogoutResponse::class,
            'LogoutRequest' => LogoutRequest::class,
            'Response' => Response::class,
            'ArtifactResponse' => null,
            'ArtifactResolve' => null,
        ];

        $rootElementName = $node->localName;

        if (array_key_exists($rootElementName, $map)) {
            if ($class = $map[$rootElementName]) {
                /** @var SamlMessage $result */
                $result = new $class();
            } else {
                throw new LogicException('Deserialization of %s root element is not implemented');
            }
        } else {
            throw new LightSamlXmlException(sprintf("Unknown SAML message '%s'", $rootElementName));
        }

        $result->deserialize($node, $context);

        return $result;
    }

    public function setID(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getID(): ?string
    {
        return $this->id;
    }

    public function setIssueInstant(int|string|DateTime $issueInstant): static
    {
        $this->issueInstant = Helper::getTimestampFromValue($issueInstant);

        return $this;
    }

    public function getIssueInstantTimestamp(): ?int
    {
        return $this->issueInstant;
    }

    public function getIssueInstantString(): ?string
    {
        if ($this->issueInstant) {
            return Helper::time2string($this->issueInstant);
        }

        return null;
    }

    public function getIssueInstantDateTime(): ?DateTime
    {
        if ($this->issueInstant) {
            return new DateTime('@' . $this->issueInstant);
        }

        return null;
    }

    public function setVersion(string $version): static
    {
        $this->version = $version;

        return $this;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setDestination(?string $destination): static
    {
        $this->destination = $destination;

        return $this;
    }

    public function getDestination(): ?string
    {
        return $this->destination;
    }

    public function setIssuer(?Issuer $issuer = null): static
    {
        $this->issuer = $issuer;

        return $this;
    }

    public function getIssuer(): ?Issuer
    {
        return $this->issuer;
    }

    public function setConsent(?string $consent): static
    {
        $this->consent = $consent;

        return $this;
    }

    public function getConsent(): ?string
    {
        return $this->consent;
    }

    public function setSignature(?Signature $signature = null): static
    {
        $this->signature = $signature;

        return $this;
    }

    public function getSignature(): ?Signature
    {
        return $this->signature;
    }

    public function setRelayState(?string $relayState): static
    {
        $this->relayState = $relayState;

        return $this;
    }

    public function getRelayState(): ?string
    {
        return $this->relayState;
    }

    public function serialize(DOMNode $parent, SerializationContext $context): void
    {
        $this->attributesToXml(['ID', 'Version', 'IssueInstant', 'Destination', 'Consent'], $parent);

        $this->singleElementsToXml(['Issuer'], $parent, $context);
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): void
    {
        $this->attributesFromXml($node, ['ID', 'Version', 'IssueInstant', 'Destination', 'Consent']);

        $this->singleElementsFromXml($node, $context, [
            'Issuer' => ['saml', Issuer::class],
            'Signature' => ['ds', SignatureXmlReader::class],
        ]);
    }
}
