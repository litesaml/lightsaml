<?php

namespace LightSaml\Model\Assertion;

use DOMElement;
use DOMNode;
use LightSaml\Error\LightSamlException;
use LightSaml\Model\AbstractSamlModel;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Context\SerializationContext;
use LogicException;
use RobRichards\XMLSecLibs\XMLSecEnc;
use RobRichards\XMLSecLibs\XMLSecurityKey;

abstract class EncryptedElementWriter extends EncryptedElement
{
    /** @var DOMElement */
    protected $encryptedElement;

    /**
     * @param string $blockEncryptionAlgorithm
     * @param string $keyTransportEncryption
     */
    public function __construct(protected $blockEncryptionAlgorithm = XMLSecurityKey::AES128_CBC, protected $keyTransportEncryption = XMLSecurityKey::RSA_1_5)
    {
    }

    /**
     * @return SerializationContext
     */
    public function encrypt(AbstractSamlModel $object, XMLSecurityKey $key)
    {
        $oldKey = $key;
        $key = new XMLSecurityKey($this->keyTransportEncryption, ['type' => 'public']);
        $key->loadKey($oldKey->key);

        $serializationContext = new SerializationContext();
        $object->serialize($serializationContext->getDocument(), $serializationContext);

        $enc = new XMLSecEnc();
        $enc->setNode($serializationContext->getDocument()->firstChild);
        $enc->type = XMLSecEnc::Element;

        switch ($key->type) {
            case XMLSecurityKey::TRIPLEDES_CBC:
            case XMLSecurityKey::AES128_CBC:
            case XMLSecurityKey::AES192_CBC:
            case XMLSecurityKey::AES256_CBC:
                $symmetricKey = $key;
                break;

            case XMLSecurityKey::RSA_1_5:
            case XMLSecurityKey::RSA_SHA1:
            case XMLSecurityKey::RSA_SHA256:
            case XMLSecurityKey::RSA_SHA384:
            case XMLSecurityKey::RSA_SHA512:
            case XMLSecurityKey::RSA_OAEP_MGF1P:
                $symmetricKey = new XMLSecurityKey($this->blockEncryptionAlgorithm);
                $symmetricKey->generateSessionKey();

                $enc->encryptKey($key, $symmetricKey);

                break;

            default:
                throw new LightSamlException(sprintf('Unknown key type for encryption: "%s"', $key->type));
        }

        $this->encryptedElement = $enc->encryptNode($symmetricKey);

        return $serializationContext;
    }

    /**
     * @return DOMElement
     */
    abstract protected function createRootElement(DOMNode $parent, SerializationContext $context);

    /**
     * @return void
     */
    public function serialize(DOMNode $parent, SerializationContext $context)
    {
        if (null === $this->encryptedElement) {
            throw new LightSamlException('Encrypted element missing');
        }

        $root = $this->createRootElement($parent, $context);

        $root->appendChild($context->getDocument()->importNode($this->encryptedElement, true));
    }

    public function deserialize(DOMNode $node, DeserializationContext $context)
    {
        throw new LogicException('EncryptedElementWriter can not be used for deserialization');
    }
}
