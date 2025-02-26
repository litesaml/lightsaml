<?php

namespace LightSaml\Model\XmlDSig;

use InvalidArgumentException;
use LightSaml\Credential\CredentialInterface;
use LightSaml\Credential\KeyHelper;
use LightSaml\Error\LightSamlSecurityException;
use RobRichards\XMLSecLibs\XMLSecurityKey;

abstract class AbstractSignatureReader extends Signature
{
    /** @var XMLSecurityKey|null */
    protected $key;

    /**
     * @return bool True if validated, False if validation was not performed
     *
     * @throws LightSamlSecurityException If validation fails
     */
    abstract public function validate(XMLSecurityKey $key);

    /**
     * @return XMLSecurityKey|null
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param CredentialInterface[] $credentialCandidates
     *
     * @throws InvalidArgumentException   If element of $credentialCandidates array is not CredentialInterface
     * @throws LightSamlSecurityException If validation fails
     *
     * @return CredentialInterface|null Returns credential that validated the signature or null if validation was not performed
     */
    public function validateMulti(array $credentialCandidates)
    {
        $lastException = null;

        foreach ($credentialCandidates as $credential) {
            if (false == $credential instanceof CredentialInterface) {
                throw new InvalidArgumentException('Expected CredentialInterface');
            }
            if (null == $credential->getPublicKey()) {
                continue;
            }

            try {
                $result = $this->validate($credential->getPublicKey());

                if (false === $result) {
                    return;
                }

                return $credential;
            } catch (LightSamlSecurityException $ex) {
                $lastException = $ex;
            }
        }

        if ($lastException instanceof LightSamlSecurityException) {
            throw $lastException;
        } else {
            throw new LightSamlSecurityException('No public key available for signature verification');
        }
    }

    /**
     * @return string
     */
    abstract public function getAlgorithm();

    /**
     * @return XMLSecurityKey
     */
    protected function castKeyIfNecessary(XMLSecurityKey $key)
    {
        $algorithm = $this->getAlgorithm();

        if (
            !in_array($algorithm, [
            XMLSecurityKey::RSA_SHA1,
            XMLSecurityKey::RSA_SHA256,
            XMLSecurityKey::RSA_SHA384,
            XMLSecurityKey::RSA_SHA512,
            ], true)
        ) {
            throw new LightSamlSecurityException(sprintf('Unsupported signing algorithm: "%s"', $algorithm));
        }

        if ($algorithm != $key->type) {
            $key = KeyHelper::castKey($key, $algorithm);
        }

        return $key;
    }
}
