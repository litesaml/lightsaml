<?php

namespace LightSaml\Build\Container;

use LightSaml\Binding\BindingFactoryInterface;
use LightSaml\Resolver\Credential\CredentialResolverInterface;
use LightSaml\Resolver\Endpoint\EndpointResolverInterface;
use LightSaml\Resolver\Session\SessionProcessorInterface;
use LightSaml\Resolver\Signature\SignatureResolverInterface;
use LightSaml\Validator\Model\Assertion\AssertionTimeValidator;
use LightSaml\Validator\Model\Assertion\AssertionValidatorInterface;
use LightSaml\Validator\Model\NameId\NameIdValidatorInterface;
use LightSaml\Validator\Model\Signature\SignatureValidatorInterface;

interface ServiceContainerInterface
{
    public function getAssertionValidator(): AssertionValidatorInterface;

    public function getAssertionTimeValidator(): AssertionTimeValidator;

    public function getSignatureResolver(): SignatureResolverInterface;

    public function getEndpointResolver(): EndpointResolverInterface;

    public function getNameIdValidator(): NameIdValidatorInterface;

    public function getBindingFactory(): BindingFactoryInterface;

    public function getSignatureValidator(): SignatureValidatorInterface;

    public function getCredentialResolver(): CredentialResolverInterface;

    public function getSessionProcessor(): SessionProcessorInterface;
}
