<?php

namespace LightSaml\Action\Profile\Inbound\Message;

use LightSaml\Action\Profile\AbstractProfileAction;
use LightSaml\Context\Profile\Helper\LogHelper;
use LightSaml\Context\Profile\Helper\MessageContextHelper;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Criteria\CriteriaSet;
use LightSaml\Error\LightSamlContextException;
use LightSaml\Model\Metadata\IdpSsoDescriptor;
use LightSaml\Model\Metadata\SpSsoDescriptor;
use LightSaml\Resolver\Endpoint\Criteria\DescriptorTypeCriteria;
use LightSaml\Resolver\Endpoint\Criteria\LocationCriteria;
use LightSaml\Resolver\Endpoint\EndpointResolverInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractDestinationValidatorAction extends AbstractProfileAction
{
    public function __construct(LoggerInterface $logger, protected EndpointResolverInterface $endpointResolver)
    {
        parent::__construct($logger);
    }

    /**
     * @return void
     */
    protected function doExecute(ProfileContext $context)
    {
        $message = MessageContextHelper::asSamlMessage($context->getInboundContext());
        $destination = $message->getDestination();

        if (null == $destination) {
            return;
        }

        $criteriaSet = $this->getCriteriaSet($context, $destination);
        $endpoints = $this->endpointResolver->resolve($criteriaSet, $context->getOwnEntityDescriptor()->getAllEndpoints());

        if ($endpoints) {
            return;
        }

        $message = sprintf('Invalid inbound message destination "%s"', $destination);
        $this->logger->emergency($message, LogHelper::getActionErrorContext($context, $this));
        throw new LightSamlContextException($context, $message);
    }

    /**
     * @param string $location
     *
     * @return CriteriaSet
     */
    protected function getCriteriaSet(ProfileContext $context, $location)
    {
        return new CriteriaSet([
            new DescriptorTypeCriteria(
                ProfileContext::ROLE_IDP === $context->getOwnRole()
                ? IdpSsoDescriptor::class
                : SpSsoDescriptor::class
            ),
            new LocationCriteria($location),
        ]);
    }
}
