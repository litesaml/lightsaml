<?php

namespace LightSaml\Model\Assertion;

use DOMDocument;
use DOMElement;
use DOMNode;
use Exception;
use InvalidArgumentException;
use LightSaml\Context\Model\DeserializationContext;
use LightSaml\Context\Model\SerializationContext;
use LightSaml\Credential\CredentialInterface;
use LightSaml\Error\LightSamlSecurityException;
use LightSaml\Error\LightSamlXmlException;
use LogicException;
use RobRichards\XMLSecLibs\XMLSecEnc;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class EncryptedElementReader extends EncryptedElement
{
    protected XMLSecEnc $xmlEnc;

    protected XMLSecurityKey $symmetricKey;

    protected XMLSecurityKey $symmetricKeyInfo;

    public function getSymmetricKey(): XMLSecurityKey
    {
        return $this->symmetricKey;
    }

    public function getSymmetricKeyInfo(): XMLSecurityKey
    {
        return $this->symmetricKeyInfo;
    }

    /**
     * @throws LogicException
     */
    public function serialize(DOMNode $parent, SerializationContext $context): never
    {
        throw new LogicException('EncryptedElementReader can not be used for serialization');
    }

    public function deserialize(DOMNode $node, DeserializationContext $context): void
    {
        $list = $context->getXpath()->query('xenc:EncryptedData', $node);
        if (0 == $list->length) {
            throw new LightSamlXmlException('Missing encrypted data in <saml:EncryptedAssertion>');
        }
        if (1 != $list->length) {
            throw new LightSamlXmlException('More than one encrypted data element in <saml:EncryptedAssertion>');
        }

        /** @var DOMElement $encryptedData */
        $encryptedData = $list->item(0);
        $this->xmlEnc = new XMLSecEnc();
        $this->xmlEnc->setNode($encryptedData);
        $this->xmlEnc->type = $encryptedData->getAttribute('Type');

        $this->symmetricKey = $this->loadSymmetricKey();

        $this->symmetricKeyInfo = $this->loadSymmetricKeyInfo($this->symmetricKey);
    }

    /**
     * @param XMLSecurityKey[] $inputKeys
     *
     * @throws LogicException
     * @throws LightSamlXmlException
     * @throws LightSamlSecurityException
     */
    public function decryptMulti(array $inputKeys): DOMElement
    {
        $lastException = null;

        foreach ($inputKeys as $key) {
            if ($key instanceof CredentialInterface) {
                $key = $key->getPrivateKey();
            }
            if (false == $key instanceof XMLSecurityKey) {
                throw new InvalidArgumentException('Expected XMLSecurityKey');
            }

            try {
                return $this->decrypt($key);
            } catch (Exception $ex) {
                $lastException = $ex;
            }
        }

        if ($lastException) {
            throw $lastException;
        }

        throw new LightSamlSecurityException('No key provided for decryption');
    }

    /**
     * @throws LogicException
     * @throws LightSamlXmlException
     * @throws LightSamlSecurityException
     */
    public function decrypt(XMLSecurityKey $inputKey): DOMElement
    {
        $this->symmetricKey = $this->loadSymmetricKey();
        $this->symmetricKeyInfo = $this->loadSymmetricKeyInfo($this->symmetricKey);

        if ($this->symmetricKeyInfo->isEncrypted) {
            $this->decryptSymmetricKey($inputKey);
        } else {
            $this->symmetricKey = $inputKey;
        }

        $decrypted = $this->decryptCipher();

        return $this->buildXmlElement($decrypted);
    }

    protected function buildXmlElement(string $decrypted): DOMElement
    {
        /*
         * This is a workaround for the case where only a subset of the XML
         * tree was serialized for encryption. In that case, we may miss the
         * namespaces needed to parse the XML.
         */
        $xml = sprintf(
            '<root xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">%s</root>',
            $decrypted
        );
        $newDoc = new DOMDocument();
        if (false == @$newDoc->loadXML($xml)) {
            throw new LightSamlXmlException('Failed to parse decrypted XML. Maybe the wrong sharedkey was used?');
        }
        $decryptedElement = $newDoc->firstChild->firstChild;
        if (null == $decryptedElement) {
            throw new LightSamlSecurityException('Missing encrypted element.');
        }

        if (false == $decryptedElement instanceof DOMElement) {
            throw new LightSamlXmlException('Decrypted element was not actually a DOMElement.');
        }

        return $decryptedElement;
    }

    /**
     * @throws Exception
     */
    protected function decryptCipher(): string
    {
        $decrypted = $this->xmlEnc->decryptNode($this->symmetricKey, false);
        if (false == is_string($decrypted)) {
            throw new LogicException('Expected decrypted string');
        }

        return $decrypted;
    }

    /**
     * @throws Exception
     */
    protected function decryptSymmetricKey(XMLSecurityKey $inputKey): void
    {
        $encKey = $this->symmetricKeyInfo->encryptedCtx;
        $this->symmetricKeyInfo->key = $inputKey->key;

        $keySize = $this->symmetricKey->getSymmetricKeySize();
        if (null === $keySize) {
            // To protect against "key oracle" attacks, we need to be able to create a
            // symmetric key, and for that we need to know the key size.
            throw new LightSamlSecurityException(sprintf("Unknown key size for encryption algorithm: '%s'", $this->symmetricKey->type));
        }

        $key = $encKey->decryptKey($this->symmetricKeyInfo);
        if (!is_string($key)) {
            throw new LogicException('Expected string from decryptKey');
        }
        if (strlen($key) != $keySize) {
            throw new LightSamlSecurityException(sprintf("Unexpected key size of '%s' bits for encryption algorithm '%s', expected '%s' bits size", strlen($key) * 8, $this->symmetricKey->type, $keySize));
        }

        $this->symmetricKey->loadkey($key);
    }

    /**
     * @throws LightSamlXmlException
     */
    protected function loadSymmetricKey(): XMLSecurityKey
    {
        $symmetricKey = $this->xmlEnc->locateKey();
        if (false == $symmetricKey) {
            throw new LightSamlXmlException('Could not locate key algorithm in encrypted data');
        }

        return $symmetricKey;
    }

    /**
     * @throws LightSamlXmlException
     */
    protected function loadSymmetricKeyInfo(XMLSecurityKey $symmetricKey): XMLSecurityKey
    {
        $symmetricKeyInfo = $this->xmlEnc->locateKeyInfo($symmetricKey);
        if (false == $symmetricKeyInfo) {
            throw new LightSamlXmlException('Could not locate <dsig:KeyInfo> for the encrypted key');
        }

        return $symmetricKeyInfo;
    }
}
