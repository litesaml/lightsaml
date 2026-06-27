<?php

namespace LightSaml\Context\Profile\Helper;

use LightSaml\Context\Profile\MessageContext;
use LightSaml\Error\LightSamlContextException;
use LightSaml\Model\Protocol\AbstractRequest;
use LightSaml\Model\Protocol\AuthnRequest;
use LightSaml\Model\Protocol\LogoutRequest;
use LightSaml\Model\Protocol\LogoutResponse;
use LightSaml\Model\Protocol\Response;
use LightSaml\Model\Protocol\SamlMessage;
use LightSaml\Model\Protocol\StatusResponse;

abstract class MessageContextHelper
{
    public static function asSamlMessage(MessageContext $context): SamlMessage
    {
        $message = $context->getMessage();
        if ($message instanceof SamlMessage) {
            return $message;
        }

        throw new LightSamlContextException($context, 'Missing SamlMessage');
    }

    public static function asAuthnRequest(MessageContext $context): AuthnRequest
    {
        $message = $context->getMessage();
        if ($message instanceof AuthnRequest) {
            return $message;
        }

        throw new LightSamlContextException($context, 'Expected AuthnRequest');
    }

    public static function asAbstractRequest(MessageContext $context): AbstractRequest
    {
        $message = $context->getMessage();
        if ($message instanceof AbstractRequest) {
            return $message;
        }

        throw new LightSamlContextException($context, 'Expected AbstractRequest');
    }

    public static function asResponse(MessageContext $context): Response
    {
        $message = $context->getMessage();
        if ($message instanceof Response) {
            return $message;
        }

        throw new LightSamlContextException($context, 'Expected Response');
    }

    public static function asStatusResponse(MessageContext $context): StatusResponse
    {
        $message = $context->getMessage();
        if ($message instanceof StatusResponse) {
            return $message;
        }

        throw new LightSamlContextException($context, 'Expected StatusResponse');
    }

    public static function asLogoutRequest(MessageContext $context): LogoutRequest
    {
        $message = $context->getMessage();
        if ($message instanceof LogoutRequest) {
            return $message;
        }

        throw new LightSamlContextException($context, 'Expected LogoutRequest');
    }

    public static function asLogoutResponse(MessageContext $context): LogoutResponse
    {
        $message = $context->getMessage();
        if ($message instanceof LogoutResponse) {
            return $message;
        }

        throw new LightSamlContextException($context, 'Expected LogoutResponse');
    }
}
