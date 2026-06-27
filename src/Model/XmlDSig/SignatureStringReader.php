<?php

namespace LightSaml\Model\XmlDSig;

use DOMNode;
use LightSaml\Context\Model\DeserializationContext;
use LightSaml\Context\Model\SerializationContext;
use LightSaml\Error\LightSamlSecurityException;
use LogicException;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class SignatureStringReader extends AbstractSignatureReader
{
    public function __construct(protected ?string $signature = null, protected ?string $algorithm = null, protected ?string $data = null)
    {
    }

    public function setAlgorithm(string $algorithm): void
    {
        $this->algorithm = $algorithm;
    }

    public function getAlgorithm(): string
    {
        return $this->algorithm;
    }

    public function setData(string $data): void
    {
        $this->data = $data;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function setSignature(string $signature): void
    {
        $this->signature = $signature;
    }

    public function getSignature(): ?string
    {
        return $this->signature;
    }

    /**
     * @return bool True if validated, False if validation was not performed
     *
     * @throws LightSamlSecurityException If validation fails
     */
    public function validate(XMLSecurityKey $key): bool
    {
        if (null == $this->getSignature()) {
            return false;
        }

        $key = $this->castKeyIfNecessary($key);

        $signature = base64_decode($this->getSignature(), true);

        if (false == $key->verifySignature($this->getData(), $signature)) {
            throw new LightSamlSecurityException('Unable to validate signature on query string');
        }

        return true;
    }

    /**
     * @throws LogicException
     */
    public function serialize(DOMNode $parent, SerializationContext $context): never
    {
        throw new LogicException('SignatureStringReader can not be serialized');
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): never
    {
        throw new LogicException('SignatureStringReader can not be deserialized');
    }
}
