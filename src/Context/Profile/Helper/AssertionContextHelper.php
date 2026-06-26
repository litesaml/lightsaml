<?php

namespace LightSaml\Context\Profile\Helper;

use LightSaml\Context\Profile\AssertionContext;
use LightSaml\Error\LightSamlContextException;
use LightSaml\Model\Assertion\EncryptedAssertionReader;

abstract class AssertionContextHelper
{
    public static function getEncryptedAssertionReader(AssertionContext $context): \LightSaml\Model\Assertion\EncryptedAssertionReader
    {
        $result = $context->getEncryptedAssertion();
        if ($result instanceof EncryptedAssertionReader) {
            return $result;
        }

        throw new LightSamlContextException($context, 'Expected EncryptedAssertionReader');
    }
}
