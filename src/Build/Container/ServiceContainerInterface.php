<?php

namespace LightSaml\Build\Container;

use LightSaml\Binding\BindingFactoryInterface;
use LightSaml\Logout\Resolver\Logout\LogoutSessionResolverInterface;
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
    public function getAssertionValidator(): \LightSaml\Validator\Model\Assertion\AssertionValidatorInterface;

    public function getAssertionTimeValidator(): \LightSaml\Validator\Model\Assertion\AssertionTimeValidator;

    public function getSignatureResolver(): \LightSaml\Resolver\Signature\SignatureResolverInterface;

    public function getEndpointResolver(): \LightSaml\Resolver\Endpoint\EndpointResolverInterface;

    public function getNameIdValidator(): \LightSaml\Validator\Model\NameId\NameIdValidatorInterface;

    public function getBindingFactory(): \LightSaml\Binding\BindingFactoryInterface;

    public function getSignatureValidator(): \LightSaml\Validator\Model\Signature\SignatureValidatorInterface;

    public function getCredentialResolver(): \LightSaml\Resolver\Credential\CredentialResolverInterface;

    public function getLogoutSessionResolver(): LogoutSessionResolverInterface;

    public function getSessionProcessor(): \LightSaml\Resolver\Session\SessionProcessorInterface;
}
