<?php

namespace LightSaml\Store\Credential;

use LightSaml\Credential\Context\CredentialContextSet;
use LightSaml\Credential\Context\MetadataCredentialContext;
use LightSaml\Credential\CredentialInterface;
use LightSaml\Credential\X509Credential;
use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\Model\Metadata\SSODescriptor;
use LightSaml\Store\EntityDescriptor\EntityDescriptorStoreInterface;

class MetadataCredentialStore implements CredentialStoreInterface
{
    public function __construct(protected EntityDescriptorStoreInterface $entityDescriptorProvider)
    {
    }

    /**
     * @param string $entityId
     *
     * @return CredentialInterface[]
     */
    public function getByEntityId($entityId)
    {
        $entityDescriptor = $this->entityDescriptorProvider->get($entityId);
        if (false == $entityDescriptor) {
            return [];
        }

        return $this->extractCredentials($entityDescriptor);
    }

    /**
     * @return CredentialInterface[]
     */
    protected function extractCredentials(EntityDescriptor $entityDescriptor)
    {
        $result = [];

        foreach ($entityDescriptor->getAllIdpSsoDescriptors() as $idpDescriptor) {
            $this->handleDescriptor($idpDescriptor, $entityDescriptor, $result);
        }
        foreach ($entityDescriptor->getAllSpSsoDescriptors() as $spDescriptor) {
            $this->handleDescriptor($spDescriptor, $entityDescriptor, $result);
        }

        return $result;
    }

    protected function handleDescriptor(SSODescriptor $ssoDescriptor, EntityDescriptor $entityDescriptor, array &$result)
    {
        foreach ($ssoDescriptor->getAllKeyDescriptors() as $keyDescriptor) {
            $credential = (new X509Credential($keyDescriptor->getCertificate()))
                ->setEntityId($entityDescriptor->getEntityID())
                ->addKeyName($keyDescriptor->getCertificate()->getName())
                ->setCredentialContext(new CredentialContextSet([
                    new MetadataCredentialContext($keyDescriptor, $ssoDescriptor, $entityDescriptor),
                ]))
                ->setUsageType($keyDescriptor->getUse())
            ;

            $result[] = $credential;
        }
    }
}
