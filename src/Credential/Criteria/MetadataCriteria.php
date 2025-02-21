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

    /**
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * @return string
     */
    public function getMetadataType()
    {
        return $this->metadataType;
    }
}
