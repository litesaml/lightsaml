<?php

namespace LightSaml\Model\Assertion;

use DOMElement;
use DOMNode;
use LightSaml\Context\Model\SerializationContext;
use LightSaml\SamlConstants;

class EncryptedAssertionWriter extends EncryptedElementWriter
{
    protected function createRootElement(DOMNode $parent, SerializationContext $context): DOMElement
    {
        return $this->createElement('saml:EncryptedAssertion', SamlConstants::NS_ASSERTION, $parent, $context);
    }
}
