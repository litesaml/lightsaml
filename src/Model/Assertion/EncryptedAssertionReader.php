<?php

namespace LightSaml\Model\Assertion;

use LightSaml\Model\Context\DeserializationContext;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class EncryptedAssertionReader extends EncryptedElementReader
{
    /**
     * @param XMLSecurityKey[] $inputKeys
     *
     * @return Assertion
     */
    public function decryptMultiAssertion(array $inputKeys, DeserializationContext $deserializationContext)
    {
        $dom = $this->decryptMulti($inputKeys);

        return $this->getAssertionFromDom($dom, $deserializationContext);
    }

    /**
     * @param XMLSecurityKey $credential
     *
     * @return Assertion
     */
    public function decryptAssertion($credential, DeserializationContext $deserializationContext)
    {
        $dom = $this->decrypt($credential);

        return $this->getAssertionFromDom($dom, $deserializationContext);
    }

    /**
     * @return Assertion
     */
    protected function getAssertionFromDom(\DOMElement $dom, DeserializationContext $deserializationContext)
    {
        $deserializationContext->setDocument($dom->ownerDocument);

        $assertion = new Assertion();
        $assertion->deserialize($dom, $deserializationContext);

        return $assertion;
    }
}
