<?php

namespace LightSaml\Model\Assertion;

use DOMElement;
use LightSaml\Context\Model\DeserializationContext;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class EncryptedAssertionReader extends EncryptedElementReader
{
    /**
     * @param XMLSecurityKey[] $inputKeys
     */
    public function decryptMultiAssertion(array $inputKeys, DeserializationContext $deserializationContext): Assertion
    {
        $dom = $this->decryptMulti($inputKeys);

        return $this->getAssertionFromDom($dom, $deserializationContext);
    }

    public function decryptAssertion(XMLSecurityKey $credential, DeserializationContext $deserializationContext): Assertion
    {
        $dom = $this->decrypt($credential);

        return $this->getAssertionFromDom($dom, $deserializationContext);
    }

    protected function getAssertionFromDom(DOMElement $dom, DeserializationContext $deserializationContext): Assertion
    {
        $deserializationContext->setDocument($dom->ownerDocument);

        $assertion = new Assertion();
        $assertion->deserialize($dom, $deserializationContext);

        return $assertion;
    }
}
