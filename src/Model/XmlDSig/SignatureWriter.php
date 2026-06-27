<?php

namespace LightSaml\Model\XmlDSig;

use DOMNode;
use LightSaml\Credential\X509Certificate;
use LightSaml\Meta\SigningOptions;
use LightSaml\Context\Model\DeserializationContext;
use LightSaml\Context\Model\SerializationContext;
use LightSaml\SamlConstants;
use LogicException;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class SignatureWriter extends Signature
{
    protected string $canonicalMethod = XMLSecurityDSig::EXC_C14N;

    protected ?SigningOptions $signingOptions = null;

    public static function create(SigningOptions $options): self
    {
        $writer = new self($options->getCertificate(), $options->getPrivateKey());
        $writer->signingOptions = $options;

        return $writer;
    }

    public static function createByKeyAndCertificate(X509Certificate $certificate, XMLSecurityKey $xmlSecurityKey): SignatureWriter
    {
        $signingOptions = new SigningOptions($xmlSecurityKey, $certificate);

        return self::create($signingOptions);
    }

    public function __construct(protected ?X509Certificate $certificate = null, protected ?XMLSecurityKey $xmlSecurityKey = null, protected string $digestAlgorithm = XMLSecurityDSig::SHA256)
    {
    }

    public function getDigestAlgorithm(): string
    {
        return $this->digestAlgorithm;
    }

    public function setDigestAlgorithm(string $digestAlgorithm): static
    {
        $this->digestAlgorithm = $digestAlgorithm;

        return $this;
    }

    public function getSigningOptions(): ?SigningOptions
    {
        return $this->signingOptions;
    }

    public function setSigningOptions(SigningOptions $signingOptions): static
    {
        $this->signingOptions = $signingOptions;

        return $this;
    }

    public function getCanonicalMethod(): string
    {
        return $this->canonicalMethod;
    }

    public function setCanonicalMethod(string $canonicalMethod): static
    {
        $this->canonicalMethod = $canonicalMethod;

        return $this;
    }

    public function setXmlSecurityKey(XMLSecurityKey $key): static
    {
        $this->xmlSecurityKey = $key;

        return $this;
    }

    public function getXmlSecurityKey(): ?XMLSecurityKey
    {
        return $this->xmlSecurityKey;
    }

    public function setCertificate(X509Certificate $certificate): static
    {
        $this->certificate = $certificate;

        return $this;
    }

    public function getCertificate(): ?X509Certificate
    {
        return $this->certificate;
    }

    public function serialize(DOMNode $parent, SerializationContext $context): void
    {
        if ($this->signingOptions && false === $this->signingOptions->isEnabled()) {
            return;
        }

        $objXMLSecDSig = new XMLSecurityDSig();
        $objXMLSecDSig->setCanonicalMethod($this->getCanonicalMethod());
        $key = $this->getXmlSecurityKey();

        $objXMLSecDSig->addReferenceList(
            [$parent],
            $this->digestAlgorithm,
            [SamlConstants::XMLSEC_TRANSFORM_ALGORITHM_ENVELOPED_SIGNATURE, XMLSecurityDSig::EXC_C14N],
            ['id_name' => $this->getIDName(), 'overwrite' => false]
        );

        $objXMLSecDSig->sign($key);

        $objXMLSecDSig->add509Cert(
            $this->getCertificate()->getData(),
            false,
            false,
            $this->signingOptions ? $this->signingOptions->getCertificateOptions()->all() : null
        );

        $firstChild = $parent->hasChildNodes() ? $parent->firstChild : null;
        if ($firstChild && 'Issuer' == $firstChild->localName) {
            // The signature node should come after the issuer node
            $firstChild = $firstChild->nextSibling;
        }
        $objXMLSecDSig->insertSignature($parent, $firstChild);
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): never
    {
        throw new LogicException('SignatureWriter can not be deserialized');
    }
}
