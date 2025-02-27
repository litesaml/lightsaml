<?php

namespace LightSaml\Model\XmlDSig;

use DOMNode;
use LightSaml\Error\LightSamlSecurityException;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LogicException;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class SignatureStringReader extends AbstractSignatureReader
{
    /**
     * @param string|null $signature
     * @param string|null $algorithm
     * @param string|null $data
     */
    public function __construct(protected $signature = null, protected $algorithm = null, protected $data = null)
    {
    }

    /**
     * @param string $algorithm
     */
    public function setAlgorithm($algorithm)
    {
        $this->algorithm = (string) $algorithm;
    }

    /**
     * @return string
     */
    public function getAlgorithm()
    {
        return $this->algorithm;
    }

    /**
     * @param string $data
     */
    public function setData($data)
    {
        $this->data = (string) $data;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $signature
     */
    public function setSignature($signature)
    {
        $this->signature = (string) $signature;
    }

    /**
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * @return bool True if validated, False if validation was not performed
     *
     * @throws LightSamlSecurityException If validation fails
     */
    public function validate(XMLSecurityKey $key)
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
