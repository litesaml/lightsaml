<?php

namespace LightSaml\Resolver\Signature;

use LightSaml\Context\Profile\AbstractProfileContext;
use LightSaml\Model\XmlDSig\SignatureWriter;

interface SignatureResolverInterface
{
    public function getSignature(AbstractProfileContext $context): ?\LightSaml\Model\XmlDSig\SignatureWriter;
}
