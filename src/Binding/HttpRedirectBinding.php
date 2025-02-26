<?php

namespace LightSaml\Binding;

use Exception;
use LightSaml\Context\Profile\Helper\MessageContextHelper;
use LightSaml\Context\Profile\MessageContext;
use LightSaml\Error\LightSamlBindingException;
use LightSaml\Model\Protocol\AbstractRequest;
use LightSaml\Model\Protocol\SamlMessage;
use LightSaml\Model\XmlDSig\SignatureStringReader;
use LightSaml\Model\XmlDSig\SignatureWriter;
use LightSaml\SamlConstants;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HttpRedirectBinding extends AbstractBinding
{
    /**
     * @param string|null $destination
     *
     * @return Response
     */
    public function send(MessageContext $context, $destination = null)
    {
        $destination = $context->getMessage()->getDestination() ?: $destination;

        $url = $this->getRedirectURL($context, $destination);

        return new RedirectResponse($url);
    }

    public function receive(Request $request, MessageContext $context)
    {
        $data = $this->parseQuery($request);

        $this->processData($data, $context);
    }

    /**
     * @throws Exception
     */
    protected function processData(array $data, MessageContext $context)
    {
        $msg = $this->getMessageStringFromData($data);
        $encoding = $this->getEncodingFromData($data);
        $msg = $this->decodeMessageString($msg, $encoding);

        $this->dispatchReceive($msg);

        $deserializationContext = $context->getDeserializationContext();
        $message = SamlMessage::fromXML($msg, $deserializationContext);

        $this->loadRelayState($message, $data);
        $this->loadSignature($message, $data);

        $context->setMessage($message);
    }

    /**
     * @return string
     *
     * @throws LightSamlBindingException
     */
    protected function getMessageStringFromData(array $data)
    {
        if (array_key_exists('SAMLRequest', $data)) {
            return $data['SAMLRequest'];
        } elseif (array_key_exists('SAMLResponse', $data)) {
            return $data['SAMLResponse'];
        } else {
            throw new LightSamlBindingException('Missing SAMLRequest or SAMLResponse parameter');
        }
    }

    /**
     * @return string
     */
    protected function getEncodingFromData(array $data)
    {
        if (array_key_exists('SAMLEncoding', $data)) {
            return $data['SAMLEncoding'];
        } else {
            return SamlConstants::ENCODING_DEFLATE;
        }
    }

    /**
     * @param string $msg
     * @param string $encoding
     *
     * @throws LightSamlBindingException
     *
     * @return string
     */
    protected function decodeMessageString($msg, $encoding)
    {
        $msg = base64_decode($msg, true);
        return match ($encoding) {
            SamlConstants::ENCODING_DEFLATE => gzinflate($msg),
            default => throw new LightSamlBindingException(sprintf("Unknown encoding '%s'", $encoding)),
        };
    }

    protected function loadRelayState(SamlMessage $message, array $data)
    {
        if (array_key_exists('RelayState', $data)) {
            $message->setRelayState($data['RelayState']);
        }
    }

    protected function loadSignature(SamlMessage $message, array $data)
    {
        if (array_key_exists('Signature', $data)) {
            if (false == array_key_exists('SigAlg', $data)) {
                throw new LightSamlBindingException('Missing signature algorithm');
            }
            $message->setSignature(
                new SignatureStringReader($data['Signature'], $data['SigAlg'], $data['SignedQuery'])
            );
        }
    }

    /**
     * @param string|null $destination
     *
     * @return string
     */
    protected function getRedirectURL(MessageContext $context, $destination)
    {
        $message = MessageContextHelper::asSamlMessage($context);
        $signature = $message->getSignature();
        if ($signature && false == $signature instanceof SignatureWriter) {
            throw new LightSamlBindingException('Signature must be SignatureWriter');
        }

        $xml = $this->getMessageEncodedXml($message, $context);
        $msg = $this->addMessageToUrl($message, $xml);
        $this->addRelayStateToUrl($msg, $message);
        $this->addSignatureToUrl($msg, $signature);

        return $this->getDestinationUrl($msg, $message, $destination);
    }

    protected function getMessageEncodedXml(SamlMessage $message, MessageContext $context): string
    {
        $message->setSignature(null);

        $serializationContext = $context->getSerializationContext();
        $message->serialize($serializationContext->getDocument(), $serializationContext);
        $xml = $serializationContext->getDocument()->saveXML();

        $this->dispatchSend($xml);

        $xml = gzdeflate($xml);

        return base64_encode($xml);
    }

    /**
     * @param string $xml
     *
     * @return string
     */
    protected function addMessageToUrl(SamlMessage $message, $xml)
    {
        $msg = $message instanceof AbstractRequest ? 'SAMLRequest=' : 'SAMLResponse=';
        return $msg . urlencode($xml);
    }

    /**
     * @param string $msg
     */
    protected function addRelayStateToUrl(&$msg, SamlMessage $message)
    {
        if (null !== $message->getRelayState()) {
            $msg .= '&RelayState=' . urlencode($message->getRelayState());
        }
    }

    /**
     * @param string $msg
     */
    protected function addSignatureToUrl(&$msg, ?SignatureWriter $signature = null)
    {
        /** @var $key XMLSecurityKey */
        $key = $signature instanceof SignatureWriter ? $signature->getXmlSecurityKey() : null;

        if (null != $key) {
            $msg .= '&SigAlg=' . urlencode($key->type);
            $signature = $key->signData($msg);
            $msg .= '&Signature=' . urlencode(base64_encode($signature));
        }
    }

    /**
     * @param string      $msg
     * @param string|null $destination
     *
     * @return string
     */
    protected function getDestinationUrl($msg, SamlMessage $message, $destination)
    {
        $destination = $message->getDestination() ?: $destination;
        if (!str_contains($destination, '?')) {
            $destination .= '?' . $msg;
        } else {
            $destination .= '&' . $msg;
        }

        return $destination;
    }

    /**
     * @return array
     */
    protected function parseQuery(Request $request)
    {
        /*
         * Parse the query string. We need to do this ourself, so that we get access
         * to the raw (urlencoded) values. This is required because different software
         * can urlencode to different values.
         */
        $sigQuery = $relayState = $sigAlg = '';
        $data = $this->parseQueryString($request->server->get('QUERY_STRING'));
        $result = [];
        foreach ($data as $name => $value) {
            $result[$name] = urldecode($value);
            switch ($name) {
                case 'SAMLRequest':
                case 'SAMLResponse':
                    $sigQuery = $name . '=' . $value;
                    break;
                case 'RelayState':
                    $relayState = '&RelayState=' . $value;
                    break;
                case 'SigAlg':
                    $sigAlg = '&SigAlg=' . $value;
                    break;
            }
        }
        $result['SignedQuery'] = $sigQuery . $relayState . $sigAlg;

        return $result;
    }

    /**
     * @param string $queryString
     *
     * @return array
     */
    protected function parseQueryString($queryString)
    {
        $result = [];
        foreach (explode('&', $queryString ?: '') as $e) {
            $tmp = explode('=', $e, 2);
            $name = $tmp[0];
            $value = 2 === count($tmp) ? $tmp[1] : '';
            $name = urldecode($name);
            $result[$name] = $value;
        }

        return $result;
    }
}
