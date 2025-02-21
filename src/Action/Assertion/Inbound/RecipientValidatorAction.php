<?php

namespace LightSaml\Action\Assertion\Inbound;

use LightSaml\Action\Assertion\AbstractAssertionAction;
use LightSaml\Context\Profile\AssertionContext;
use LightSaml\Context\Profile\Helper\LogHelper;
use LightSaml\Criteria\CriteriaSet;
use LightSaml\Error\LightSamlContextException;
use LightSaml\Model\Assertion\SubjectConfirmation;
use LightSaml\Model\Metadata\AssertionConsumerService;
use LightSaml\Model\Metadata\SpSsoDescriptor;
use LightSaml\Resolver\Endpoint\Criteria\DescriptorTypeCriteria;
use LightSaml\Resolver\Endpoint\Criteria\LocationCriteria;
use LightSaml\Resolver\Endpoint\Criteria\ServiceTypeCriteria;
use LightSaml\Resolver\Endpoint\EndpointResolverInterface;
use Psr\Log\LoggerInterface;

class RecipientValidatorAction extends AbstractAssertionAction
{
    public function __construct(LoggerInterface $logger, private readonly EndpointResolverInterface $endpointResolver)
    {
        parent::__construct($logger);
    }

    /**
     * @return void
     */
    protected function doExecute(AssertionContext $context)
    {
        if ($context->getAssertion()->getAllAuthnStatements() && $context->getAssertion()->hasBearerSubject()) {
            $this->validateBearerAssertion($context);
        }
    }

    protected function validateBearerAssertion(AssertionContext $context)
    {
        foreach ($context->getAssertion()->getSubject()->getBearerConfirmations() as $subjectConfirmation) {
            $this->validateSubjectConfirmation($context, $subjectConfirmation);
        }
    }

    protected function validateSubjectConfirmation(AssertionContext $context, SubjectConfirmation $subjectConfirmation)
    {
        $recipient = $subjectConfirmation->getSubjectConfirmationData()->getRecipient();
        if (null == $recipient) {
            $message = 'Bearer SubjectConfirmation must contain Recipient attribute';
            $this->logger->error($message, LogHelper::getActionErrorContext($context, $this));
            throw new LightSamlContextException($context, $message);
        }

        $criteriaSet = new CriteriaSet([
            new DescriptorTypeCriteria(SpSsoDescriptor::class),
            new ServiceTypeCriteria(AssertionConsumerService::class),
            new LocationCriteria($recipient),
        ]);
        $ownEntityDescriptor = $context->getProfileContext()->getOwnEntityDescriptor();
        $arrEndpoints = $this->endpointResolver->resolve($criteriaSet, $ownEntityDescriptor->getAllEndpoints());

        if (empty($arrEndpoints)) {
            $message = sprintf("Recipient '%s' does not match SP descriptor", $recipient);
            $this->logger->error($message, LogHelper::getActionErrorContext($context, $this, [
                'recipient' => $recipient,
            ]));
            throw new LightSamlContextException($context, $message);
        }
    }
}
