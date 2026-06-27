<?php

namespace LightSaml\Context\Model;

use DOMDocument;
use DOMXPath;
use LightSaml\Context\AbstractContext;
use LightSaml\SamlConstants;
use RobRichards\XMLSecLibs\XMLSecEnc;

class DeserializationContext extends AbstractContext
{
    private ?DOMDocument $document = null;

    private ?DOMXPath $xpath = null;

    public function __construct(?DOMDocument $document = null)
    {
        $this->document = $document ?: new DOMDocument();
    }

    public function getDocument(): ?DOMDocument
    {
        return $this->document;
    }

    public function setDocument(?DOMDocument $document): static
    {
        $this->document = $document;
        $this->xpath = null;

        return $this;
    }

    public function getXpath(): DOMXPath
    {
        if (null === $this->xpath) {
            $this->xpath = new DOMXPath($this->document);
            $this->xpath->registerNamespace('saml', SamlConstants::NS_ASSERTION);
            $this->xpath->registerNamespace('samlp', SamlConstants::NS_PROTOCOL);
            $this->xpath->registerNamespace('md', SamlConstants::NS_METADATA);
            $this->xpath->registerNamespace('ds', SamlConstants::NS_XMLDSIG);
            $this->xpath->registerNamespace('xenc', XMLSecEnc::XMLENCNS);
        }

        return $this->xpath;
    }
}
