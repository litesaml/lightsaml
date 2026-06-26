<?php

namespace LightSaml\Model\Assertion;

use DOMElement;
use LightSaml\Model\Context\DeserializationContext;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class EncryptedAssertionReader extends EncryptedElementReader
{
    /**
     * @param XMLSecurityKey[] $inputKeys
     */
    public function decryptMultiAssertion(array $inputKeys, DeserializationContext $deserializationContext): \LightSaml\Model\Assertion\Assertion
    {
        $dom = $this->decryptMulti($inputKeys);

        return $this->getAssertionFromDom($dom, $deserializationContext);
    }

    public function decryptAssertion(\RobRichards\XMLSecLibs\XMLSecurityKey $credential, DeserializationContext $deserializationContext): \LightSaml\Model\Assertion\Assertion
    {
        $dom = $this->decrypt($credential);

        return $this->getAssertionFromDom($dom, $deserializationContext);
    }

    protected function getAssertionFromDom(DOMElement $dom, DeserializationContext $deserializationContext): \LightSaml\Model\Assertion\Assertion
    {
        $deserializationContext->setDocument($dom->ownerDocument);

        $assertion = new Assertion();
        $assertion->deserialize($dom, $deserializationContext);

        return $assertion;
    }
}
