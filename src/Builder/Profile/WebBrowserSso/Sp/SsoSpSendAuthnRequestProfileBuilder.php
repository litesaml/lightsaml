<?php

namespace LightSaml\Builder\Profile\WebBrowserSso\Sp;

use LightSaml\Build\Container\BuildContainerInterface;
use LightSaml\Builder\Action\ActionBuilderInterface;
use LightSaml\Builder\Action\Profile\SingleSignOn\Sp\SsoSpSendAuthnRequestActionBuilder;
use LightSaml\Builder\Profile\AbstractProfileBuilder;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Meta\TrustOptions\TrustOptions;
use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\Profile\Profiles;
use RuntimeException;

class SsoSpSendAuthnRequestProfileBuilder extends AbstractProfileBuilder
{
    /**
     * @param string $idpEntityId
     */
    public function __construct(BuildContainerInterface $buildContainer, protected $idpEntityId)
    {
        parent::__construct($buildContainer);
    }

    public function buildContext()
    {
        $result = parent::buildContext();

        $idpEd = $this->container->getPartyContainer()->getIdpEntityDescriptorStore()->get($this->idpEntityId);
        if (false == $idpEd) {
            throw new RuntimeException(sprintf('Unknown IDP "%s"', $this->idpEntityId));
        }

        $trustOptions = $this->getTrustOptions($idpEd);

        $result->getPartyEntityContext()
            ->setEntityDescriptor($idpEd)
            ->setTrustOptions($trustOptions)
        ;

        return $result;
    }

    /**
     * @return string
     */
    protected function getProfileId()
    {
        return Profiles::SSO_SP_SEND_AUTHN_REQUEST;
    }

    /**
     * @return string
     */
    protected function getProfileRole()
    {
        return ProfileContext::ROLE_SP;
    }

    /**
     * @return ActionBuilderInterface
     */
    protected function getActionBuilder()
    {
        return new SsoSpSendAuthnRequestActionBuilder($this->container);
    }

    /**
     * @return TrustOptions
     */
    private function getTrustOptions(EntityDescriptor $idpEd)
    {
        $trustOptions = $this->container->getPartyContainer()->getTrustOptionsStore()->get($this->idpEntityId) ?: new TrustOptions();

        $wantAuthnRequestsSigned = $idpEd->getFirstIdpSsoDescriptor()->getWantAuthnRequestsSigned();

        if (null !== $wantAuthnRequestsSigned) {
            $trustOptions->setSignAuthnRequest($wantAuthnRequestsSigned);
        }

        return $trustOptions;
    }
}
