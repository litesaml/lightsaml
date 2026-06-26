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
    public static function asSamlMessage(MessageContext $context): \LightSaml\Model\Protocol\SamlMessage
    {
        $message = $context->getMessage();
        if ($message instanceof \LightSaml\Model\Protocol\SamlMessage) {
            return $message;
        }

        throw new LightSamlContextException($context, 'Missing SamlMessage');
    }

    public static function asAuthnRequest(MessageContext $context): \LightSaml\Model\Protocol\AuthnRequest
    {
        $message = $context->getMessage();
        if ($message instanceof AuthnRequest) {
            return $message;
        }

        throw new LightSamlContextException($context, 'Expected AuthnRequest');
    }

    public static function asAbstractRequest(MessageContext $context): \LightSaml\Model\Protocol\AbstractRequest
    {
        $message = $context->getMessage();
        if ($message instanceof AbstractRequest) {
            return $message;
        }

        throw new LightSamlContextException($context, 'Expected AbstractRequest');
    }

    public static function asResponse(MessageContext $context): \LightSaml\Model\Protocol\Response
    {
        $message = $context->getMessage();
        if ($message instanceof Response) {
            return $message;
        }

        throw new LightSamlContextException($context, 'Expected Response');
    }

    public static function asStatusResponse(MessageContext $context): \LightSaml\Model\Protocol\StatusResponse
    {
        $message = $context->getMessage();
        if ($message instanceof StatusResponse) {
            return $message;
        }

        throw new LightSamlContextException($context, 'Expected StatusResponse');
    }

    public static function asLogoutRequest(MessageContext $context): \LightSaml\Model\Protocol\LogoutRequest
    {
        $message = $context->getMessage();
        if ($message instanceof LogoutRequest) {
            return $message;
        }

        throw new LightSamlContextException($context, 'Expected LogoutRequest');
    }

    public static function asLogoutResponse(MessageContext $context): \LightSaml\Model\Protocol\LogoutResponse
    {
        $message = $context->getMessage();
        if ($message instanceof LogoutResponse) {
            return $message;
        }

        throw new LightSamlContextException($context, 'Expected LogoutResponse');
    }
}
