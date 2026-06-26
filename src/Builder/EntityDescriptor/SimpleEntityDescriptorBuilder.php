<?php

namespace LightSaml\Builder\EntityDescriptor;

use LightSaml\Credential\X509Certificate;
use LightSaml\Model\Metadata\AssertionConsumerService;
use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\Model\Metadata\IdpSsoDescriptor;
use LightSaml\Model\Metadata\KeyDescriptor;
use LightSaml\Model\Metadata\RoleDescriptor;
use LightSaml\Model\Metadata\SingleSignOnService;
use LightSaml\Model\Metadata\SpSsoDescriptor;
use LightSaml\Provider\EntityDescriptor\EntityDescriptorProviderInterface;
use LightSaml\SamlConstants;
use LogicException;

class SimpleEntityDescriptorBuilder implements EntityDescriptorProviderInterface
{
    private ?\LightSaml\Model\Metadata\EntityDescriptor $entityDescriptor = null;

    /**
     * @param string[]      $acsBindings
     * @param string[]      $ssoBindings
     * @param string[]|null $use
     */
    public function __construct(protected string $entityId, protected string $acsUrl, protected string $ssoUrl, protected X509Certificate $ownCertificate, protected array $acsBindings = [SamlConstants::BINDING_SAML2_HTTP_POST], protected array $ssoBindings = [SamlConstants::BINDING_SAML2_HTTP_POST, SamlConstants::BINDING_SAML2_HTTP_REDIRECT], protected ?array $use = [KeyDescriptor::USE_ENCRYPTION, KeyDescriptor::USE_SIGNING])
    {
    }

    public function get(): \LightSaml\Model\Metadata\EntityDescriptor
    {
        if (!$this->entityDescriptor instanceof \LightSaml\Model\Metadata\EntityDescriptor) {
            $this->entityDescriptor = $this->getEntityDescriptor();
            if (false === $this->entityDescriptor instanceof EntityDescriptor) {
                throw new LogicException('Expected EntityDescriptor');
            }
        }

        return $this->entityDescriptor;
    }

    protected function getEntityDescriptor(): \LightSaml\Model\Metadata\EntityDescriptor
    {
        $entityDescriptor = new EntityDescriptor();
        $entityDescriptor->setEntityID($this->entityId);

        $spSsoDescriptor = $this->getSpSsoDescriptor();
        if ($spSsoDescriptor instanceof \LightSaml\Model\Metadata\SpSsoDescriptor) {
            $entityDescriptor->addItem($spSsoDescriptor);
        }

        $idpSsoDescriptor = $this->getIdpSsoDescriptor();
        if ($idpSsoDescriptor instanceof \LightSaml\Model\Metadata\IdpSsoDescriptor) {
            $entityDescriptor->addItem($idpSsoDescriptor);
        }

        return $entityDescriptor;
    }

    protected function getSpSsoDescriptor(): ?\LightSaml\Model\Metadata\SpSsoDescriptor
    {
        if (null === $this->acsUrl) {
            return null;
        }

        $spSso = new SpSsoDescriptor();

        foreach ($this->acsBindings as $index => $biding) {
            $acs = new AssertionConsumerService();
            $acs->setIndex($index)->setLocation($this->acsUrl)->setBinding($biding);
            $spSso->addAssertionConsumerService($acs);
        }

        $this->addKeyDescriptors($spSso);

        return $spSso;
    }

    /**
     * @return IdpSsoDescriptor
     */
    protected function getIdpSsoDescriptor(): ?\LightSaml\Model\Metadata\IdpSsoDescriptor
    {
        if (null === $this->ssoUrl) {
            return null;
        }

        $idpSso = new IdpSsoDescriptor();

        foreach ($this->ssoBindings as $binding) {
            $sso = new SingleSignOnService();
            $sso
                ->setLocation($this->ssoUrl)
                ->setBinding($binding);
            $idpSso->addSingleSignOnService($sso);
        }

        $this->addKeyDescriptors($idpSso);

        return $idpSso;
    }

    protected function addKeyDescriptors(RoleDescriptor $descriptor)
    {
        if ($this->use) {
            foreach ($this->use as $use) {
                $kd = new KeyDescriptor();
                $kd->setUse($use);
                $kd->setCertificate($this->ownCertificate);

                $descriptor->addKeyDescriptor($kd);
            }
        } else {
            $kd = new KeyDescriptor();
            $kd->setCertificate($this->ownCertificate);

            $descriptor->addKeyDescriptor($kd);
        }
    }
}
