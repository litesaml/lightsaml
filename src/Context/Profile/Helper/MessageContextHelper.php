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
    /**
     * @return SamlMessage
     */
    public static function asSamlMessage(MessageContext $context)
    {
        $message = $context->getMessage();
        if ($message) {
            return $message;
        }

        throw new LightSamlContextException($context, 'Missing SamlMessage');
    }

    /**
     * @return AuthnRequest
     */
    public static function asAuthnRequest(MessageContext $context)
    {
        $message = $context->getMessage();
        if ($message instanceof AuthnRequest) {
            return $message;
        }

        throw new LightSamlContextException($context, 'Expected AuthnRequest');
    }

    /**
     * @return AbstractRequest
     */
    public static function asAbstractRequest(MessageContext $context)
    {
        $message = $context->getMessage();
        if ($message instanceof AbstractRequest) {
            return $message;
        }

        throw new LightSamlContextException($context, 'Expected AbstractRequest');
    }

    /**
     * @return Response
     */
    public static function asResponse(MessageContext $context)
    {
        $message = $context->getMessage();
        if ($message instanceof Response) {
            return $message;
        }

        throw new LightSamlContextException($context, 'Expected Response');
    }

    /**
     * @return StatusResponse
     */
    public static function asStatusResponse(MessageContext $context)
    {
        $message = $context->getMessage();
        if ($message instanceof StatusResponse) {
            return $message;
        }

        throw new LightSamlContextException($context, 'Expected StatusResponse');
    }

    /**
     * @return LogoutRequest
     */
    public static function asLogoutRequest(MessageContext $context)
    {
        $message = $context->getMessage();
        if ($message instanceof LogoutRequest) {
            return $message;
        }

        throw new LightSamlContextException($context, 'Expected LogoutRequest');
    }

    /**
     * @return LogoutResponse
     */
    public static function asLogoutResponse(MessageContext $context)
    {
        $message = $context->getMessage();
        if ($message instanceof LogoutResponse) {
            return $message;
        }

        throw new LightSamlContextException($context, 'Expected LogoutResponse');
    }
}
