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
    /** @var EntityDescriptor */
    private $entityDescriptor;

    /**
     * @param string        $entityId
     * @param string        $acsUrl
     * @param string        $ssoUrl
     * @param string[]      $acsBindings
     * @param string[]      $ssoBindings
     * @param string[]|null $use
     */
    public function __construct(protected $entityId, protected $acsUrl, protected $ssoUrl, protected X509Certificate $ownCertificate, protected array $acsBindings = [SamlConstants::BINDING_SAML2_HTTP_POST], protected array $ssoBindings = [SamlConstants::BINDING_SAML2_HTTP_POST, SamlConstants::BINDING_SAML2_HTTP_REDIRECT], protected $use = [KeyDescriptor::USE_ENCRYPTION, KeyDescriptor::USE_SIGNING])
    {
    }

    /**
     * @return EntityDescriptor
     */
    public function get()
    {
        if (null === $this->entityDescriptor) {
            $this->entityDescriptor = $this->getEntityDescriptor();
            if (false === $this->entityDescriptor instanceof EntityDescriptor) {
                throw new LogicException('Expected EntityDescriptor');
            }
        }

        return $this->entityDescriptor;
    }

    /**
     * @return EntityDescriptor
     */
    protected function getEntityDescriptor()
    {
        $entityDescriptor = new EntityDescriptor();
        $entityDescriptor->setEntityID($this->entityId);

        $spSsoDescriptor = $this->getSpSsoDescriptor();
        if ($spSsoDescriptor) {
            $entityDescriptor->addItem($spSsoDescriptor);
        }

        $idpSsoDescriptor = $this->getIdpSsoDescriptor();
        if ($idpSsoDescriptor) {
            $entityDescriptor->addItem($idpSsoDescriptor);
        }

        return $entityDescriptor;
    }

    /**
     * @return SpSsoDescriptor|null
     */
    protected function getSpSsoDescriptor()
    {
        if (null === $this->acsUrl) {
            return;
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
    protected function getIdpSsoDescriptor()
    {
        if (null === $this->ssoUrl) {
            return;
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
