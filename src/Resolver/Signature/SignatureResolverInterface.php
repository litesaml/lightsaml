<?php

namespace LightSaml\Resolver\Signature;

use LightSaml\Context\Profile\AbstractProfileContext;
use LightSaml\Model\XmlDSig\SignatureWriter;

interface SignatureResolverInterface
{
    /**
     * @return SignatureWriter|null
     */
    public function getSignature(AbstractProfileContext $context);
}
