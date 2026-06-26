<?php

namespace LightSaml\Credential\Criteria;

use LightSaml\SamlConstants;

class MetadataCriteria implements TrustCriteriaInterface
{
    public const TYPE_IDP = 'idp';
    public const TYPE_SP = 'sp';

    /**
     * @param string $metadataType
     * @param string $protocol
     */
    public function __construct(protected $metadataType, protected $protocol = SamlConstants::PROTOCOL_SAML2)
    {
    }

    public function getProtocol(): string
    {
        return $this->protocol;
    }

    public function getMetadataType(): string
    {
        return $this->metadataType;
    }
}
