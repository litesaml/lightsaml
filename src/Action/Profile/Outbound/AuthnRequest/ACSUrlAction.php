<?php

namespace LightSaml\Action\Profile\Outbound\AuthnRequest;

use LightSaml\Action\Profile\AbstractProfileAction;
use LightSaml\Context\Profile\Helper\LogHelper;
use LightSaml\Context\Profile\Helper\MessageContextHelper;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Criteria\CriteriaSet;
use LightSaml\Error\LightSamlContextException;
use LightSaml\Model\Metadata\AssertionConsumerService;
use LightSaml\Model\Metadata\SpSsoDescriptor;
use LightSaml\Resolver\Endpoint\Criteria\BindingCriteria;
use LightSaml\Resolver\Endpoint\Criteria\DescriptorTypeCriteria;
use LightSaml\Resolver\Endpoint\Criteria\ServiceTypeCriteria;
use LightSaml\Resolver\Endpoint\EndpointResolverInterface;
use LightSaml\SamlConstants;
use Psr\Log\LoggerInterface;

// TODO ACSUrlAction not used in profile builder, has to be added
class ACSUrlAction extends AbstractProfileAction
{
    public function __construct(LoggerInterface $logger, private readonly EndpointResolverInterface $endpointResolver)
    {
        parent::__construct($logger);
    }

    protected function doExecute(ProfileContext $context)
    {
        $ownEntityDescriptor = $context->getOwnEntityDescriptor();

        $criteriaSet = new CriteriaSet([
            new DescriptorTypeCriteria(SpSsoDescriptor::class),
            new ServiceTypeCriteria(AssertionConsumerService::class),
            new BindingCriteria([SamlConstants::BINDING_SAML2_HTTP_POST]),
        ]);

        $endpoints = $this->endpointResolver->resolve($criteriaSet, $ownEntityDescriptor->getAllEndpoints());
        if (empty($endpoints)) {
            $message = 'Missing ACS Service with HTTP POST binding in own SP SSO Descriptor';
            $this->logger->error($message, LogHelper::getActionErrorContext($context, $this));
            throw new LightSamlContextException($context, $message);
        }

        MessageContextHelper::asAuthnRequest($context->getOutboundContext())
            ->setAssertionConsumerServiceURL($endpoints[0]->getEndpoint()->getLocation());
    }
}
