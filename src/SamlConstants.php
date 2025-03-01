<?php

namespace LightSaml;

abstract class SamlConstants
{
    public const PROTOCOL_SAML2 = 'urn:oasis:names:tc:SAML:2.0:protocol';
    public const PROTOCOL_SAML1 = 'urn:oasis:names:tc:SAML:1.0:protocol';
    public const PROTOCOL_SAML11 = 'urn:oasis:names:tc:SAML:1.1:protocol';
    public const PROTOCOL_SHIB1 = 'urn:mace:shibboleth:1.0';
    public const PROTOCOL_WS_FED = 'http://schemas.xmlsoap.org/ws/2003/07/secext???';

    public const VERSION_20 = '2.0';

    public const NS_PROTOCOL = 'urn:oasis:names:tc:SAML:2.0:protocol';
    public const NS_METADATA = 'urn:oasis:names:tc:SAML:2.0:metadata';
    public const NS_ASSERTION = 'urn:oasis:names:tc:SAML:2.0:assertion';
    public const NS_XMLDSIG = 'http://www.w3.org/2000/09/xmldsig#';

    public const NAME_ID_FORMAT_NONE = null;
    public const NAME_ID_FORMAT_ENTITY = 'urn:oasis:names:tc:SAML:2.0:nameid-format:entity';
    public const NAME_ID_FORMAT_PERSISTENT = 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent';
    public const NAME_ID_FORMAT_TRANSIENT = 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient';
    public const NAME_ID_FORMAT_EMAIL = 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress';
    public const NAME_ID_FORMAT_SHIB_NAME_ID = 'urn:mace:shibboleth:1.0:nameIdentifier';
    public const NAME_ID_FORMAT_X509_SUBJECT_NAME = 'urn:oasis:names:tc:SAML:1.1:nameid-format:X509SubjectName';
    public const NAME_ID_FORMAT_WINDOWS = 'urn:oasis:names:tc:SAML:1.1:nameid-format:WindowsDomainQualifiedName';
    public const NAME_ID_FORMAT_KERBEROS = 'urn:oasis:names:tc:SAML:2.0:nameid-format:kerberos';
    public const NAME_ID_FORMAT_UNSPECIFIED = 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified';

    public const BINDING_SAML2_HTTP_REDIRECT = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect';
    public const BINDING_SAML2_HTTP_POST = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST';
    public const BINDING_SAML2_HTTP_ARTIFACT = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact';
    public const BINDING_SAML2_SOAP = 'urn:oasis:names:tc:SAML:2.0:bindings:SOAP';
    public const BINDING_SAML2_HTTP_POST_SIMPLE_SIGN = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST-SimpleSign';
    public const BINDING_SHIB1_AUTHN_REQUEST = 'urn:mace:shibboleth:1.0:profiles:AuthnRequest';
    public const BINDING_SAML1_BROWSER_POST = 'urn:oasis:names:tc:SAML:1.0:profiles:browser-post';
    public const BINDING_SAML1_ARTIFACT1 = 'urn:oasis:names:tc:SAML:1.0:profiles:artifact-01';
    public const BINDING_WS_FED_WEB_SVC = 'http://schemas.xmlsoap.org/ws/2003/07/secext';

    public const STATUS_SUCCESS = 'urn:oasis:names:tc:SAML:2.0:status:Success';
    public const STATUS_REQUESTER = 'urn:oasis:names:tc:SAML:2.0:status:Requester';
    public const STATUS_RESPONDER = 'urn:oasis:names:tc:SAML:2.0:status:Responder';
    public const STATUS_VERSION_MISMATCH = 'urn:oasis:names:tc:SAML:2.0:status:VersionMismatch';
    public const STATUS_NO_PASSIVE = 'urn:oasis:names:tc:SAML:2.0:status:NoPassive';
    public const STATUS_PARTIAL_LOGOUT = 'urn:oasis:names:tc:SAML:2.0:status:PartialLogout';
    public const STATUS_PROXY_COUNT_EXCEEDED = 'urn:oasis:names:tc:SAML:2.0:status:ProxyCountExceeded';
    public const STATUS_INVALID_NAME_ID_POLICY = 'urn:oasis:names:tc:SAML:2.0:status:InvalidNameIDPolicy';
    public const STATUS_UNSUPPORTED_BINDING = 'urn:oasis:names:tc:SAML:2.0:status:UnsupportedBinding';

    public const XMLSEC_TRANSFORM_ALGORITHM_ENVELOPED_SIGNATURE = 'http://www.w3.org/2000/09/xmldsig#enveloped-signature';

    public const CONSENT_UNSPECIFIED = 'urn:oasis:names:tc:SAML:2.0:consent:unspecified';

    public const CONFIRMATION_METHOD_BEARER = 'urn:oasis:names:tc:SAML:2.0:cm:bearer';
    public const CONFIRMATION_METHOD_HOK = 'urn:oasis:names:tc:SAML:2.0:cm:holder-of-key';
    public const CONFIRMATION_METHOD_SENDER_VOUCHES = 'urn:oasis:names:tc:SAML:2.0:cm:sender-vouches';

    public const AUTHN_CONTEXT_PASSWORD = 'urn:oasis:names:tc:SAML:2.0:ac:classes:Password';
    public const AUTHN_CONTEXT_UNSPECIFIED = 'urn:oasis:names:tc:SAML:2.0:ac:classes:unspecified';
    public const AUTHN_CONTEXT_PASSWORD_PROTECTED_TRANSPORT = 'urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport';
    public const AUTHN_CONTEXT_WINDOWS = 'urn:federation:authentication:windows';

    public const ENCODING_DEFLATE = 'urn:oasis:names:tc:SAML:2.0:bindings:URL-Encoding:DEFLATE';

    public const LOGOUT_REASON_USER = 'urn:oasis:names:tc:SAML:2.0:logout:user';
    public const LOGOUT_REASON_ADMIN = 'urn:oasis:names:tc:SAML:2.0:logout:admin';
    public const LOGOUT_REASON_GLOBAL_TIMEOUT = 'urn:oasis:names:tc:SAML:2.0:logout:global-timeout';
    public const LOGOUT_REASON_SP_TIMEOUT = 'urn:oasis:names:tc:SAML:2.0:logout:sp-timeout';

    public const XMLDSIG_DIGEST_MD5 = 'http://www.w3.org/2001/04/xmldsig-more#md5';

    public const ATTRIBUTE_NAME_FORMAT_UNSPECIFIED = 'urn:oasis:names:tc:SAML:2.0:attrname-format:unspecified';

    /**
     * @param string $value
     *
     * @return bool
     */
    public static function isProtocolValid($value)
    {
        static $arr = [
            self::PROTOCOL_SAML2,
            self::PROTOCOL_SAML1,
            self::PROTOCOL_SAML11,
            self::PROTOCOL_SHIB1,
            self::PROTOCOL_WS_FED,
        ];

        return in_array($value, $arr, true);
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public static function isNsValid($value)
    {
        static $arr = [
            self::NS_PROTOCOL,
            self::NS_METADATA,
            self::NS_ASSERTION,
            self::NS_XMLDSIG,
        ];

        return in_array($value, $arr, true);
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public static function isNameIdFormatValid($value)
    {
        static $arr = [
            self::NAME_ID_FORMAT_NONE,
            self::NAME_ID_FORMAT_ENTITY,
            self::NAME_ID_FORMAT_PERSISTENT,
            self::NAME_ID_FORMAT_TRANSIENT,
            self::NAME_ID_FORMAT_EMAIL,
            self::NAME_ID_FORMAT_SHIB_NAME_ID,
            self::NAME_ID_FORMAT_X509_SUBJECT_NAME,
            self::NAME_ID_FORMAT_WINDOWS,
            self::NAME_ID_FORMAT_KERBEROS,
            self::NAME_ID_FORMAT_UNSPECIFIED,
        ];

        return in_array($value, $arr, true);
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public static function isBindingValid($value)
    {
        static $arr = [
            self::BINDING_SAML2_HTTP_REDIRECT,
            self::BINDING_SAML2_HTTP_POST,
            self::BINDING_SAML2_HTTP_ARTIFACT,
            self::BINDING_SAML2_SOAP,
            self::BINDING_SAML2_HTTP_POST_SIMPLE_SIGN,
            self::BINDING_SHIB1_AUTHN_REQUEST,
            self::BINDING_SAML1_BROWSER_POST,
            self::BINDING_SAML1_ARTIFACT1,
            self::BINDING_WS_FED_WEB_SVC,
        ];

        return in_array($value, $arr, true);
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public static function isStatusValid($value)
    {
        static $arr = [
            self::STATUS_SUCCESS,
            self::STATUS_REQUESTER,
            self::STATUS_RESPONDER,
            self::STATUS_VERSION_MISMATCH,
            self::STATUS_NO_PASSIVE,
            self::STATUS_PARTIAL_LOGOUT,
            self::STATUS_PROXY_COUNT_EXCEEDED,
            self::STATUS_INVALID_NAME_ID_POLICY,
            self::STATUS_UNSUPPORTED_BINDING,
        ];

        return in_array($value, $arr, true);
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public static function isConfirmationMethodValid($value)
    {
        static $arr = [
            self::CONFIRMATION_METHOD_BEARER,
            self::CONFIRMATION_METHOD_HOK,
            self::CONFIRMATION_METHOD_SENDER_VOUCHES,
        ];

        return in_array($value, $arr, true);
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public static function isAuthnContextValid($value)
    {
        static $arr = [
            self::AUTHN_CONTEXT_PASSWORD,
            self::AUTHN_CONTEXT_UNSPECIFIED,
            self::AUTHN_CONTEXT_PASSWORD_PROTECTED_TRANSPORT,
            self::AUTHN_CONTEXT_WINDOWS,
        ];

        return in_array($value, $arr, true);
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public static function isLogoutReasonValid($value)
    {
        static $arr = [
            self::LOGOUT_REASON_USER,
            self::LOGOUT_REASON_ADMIN,
            self::LOGOUT_REASON_GLOBAL_TIMEOUT,
            self::LOGOUT_REASON_SP_TIMEOUT,
        ];

        return in_array($value, $arr, true);
    }
}
