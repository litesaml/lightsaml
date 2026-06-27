<?php

namespace LightSaml\Binding;

use Exception;
use LightSaml\Context\Profile\Helper\MessageContextHelper;
use LightSaml\Context\Profile\MessageContext;
use LightSaml\Error\LightSamlBindingException;
use LightSaml\Error\LightSamlMissingFactoryException;
use LightSaml\Model\Protocol\AbstractRequest;
use LightSaml\Model\Protocol\SamlMessage;
use LightSaml\Model\XmlDSig\SignatureStringReader;
use LightSaml\Model\XmlDSig\SignatureWriter;
use LightSaml\SamlConstants;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class HttpRedirectBinding extends AbstractBinding
{
    public function __construct(private readonly ?ResponseFactoryInterface $responseFactory = null)
    {
    }

    public function send(MessageContext $context, ?string $destination = null): ResponseInterface
    {
        if (!$this->responseFactory instanceof ResponseFactoryInterface) {
            throw new LightSamlMissingFactoryException('ResponseFactory must be provided to use send()');
        }

        $destination = $context->getMessage()->getDestination() ?: $destination;

        $url = $this->getRedirectURL($context, $destination);

        return $this->responseFactory->createResponse(302)->withHeader('Location', $url);
    }

    public function receive(ServerRequestInterface $request, MessageContext $context): SamlMessage
    {
        $data = $this->parseQuery($request);

        $this->processData($data, $context);

        return $context->getMessage();
    }

    /**
     * @throws Exception
     */
    /** @param array<string, string> $data */
    protected function processData(array $data, MessageContext $context): void
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
     * @param array<string, string> $data
     * @throws LightSamlBindingException
     */
    protected function getMessageStringFromData(array $data): string
    {
        if (array_key_exists('SAMLRequest', $data)) {
            return $data['SAMLRequest'];
        } elseif (array_key_exists('SAMLResponse', $data)) {
            return $data['SAMLResponse'];
        } else {
            throw new LightSamlBindingException('Missing SAMLRequest or SAMLResponse parameter');
        }
    }

    /** @param array<string, string> $data */
    protected function getEncodingFromData(array $data): string
    {
        if (array_key_exists('SAMLEncoding', $data)) {
            return $data['SAMLEncoding'];
        } else {
            return SamlConstants::ENCODING_DEFLATE;
        }
    }

    /**
     *
     * @throws LightSamlBindingException
     *
     * @return string
     */
    protected function decodeMessageString(string $msg, string $encoding): string|false
    {
        $msg = base64_decode($msg, true);
        return match ($encoding) {
            SamlConstants::ENCODING_DEFLATE => gzinflate($msg),
            default => throw new LightSamlBindingException(sprintf("Unknown encoding '%s'", $encoding)),
        };
    }

    /** @param array<string, string> $data */
    protected function loadRelayState(SamlMessage $message, array $data): void
    {
        if (array_key_exists('RelayState', $data)) {
            $message->setRelayState($data['RelayState']);
        }
    }

    /** @param array<string, string> $data */
    protected function loadSignature(SamlMessage $message, array $data): void
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

    protected function getRedirectURL(MessageContext $context, ?string $destination): string
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

    protected function addMessageToUrl(SamlMessage $message, string $xml): string
    {
        $msg = $message instanceof AbstractRequest ? 'SAMLRequest=' : 'SAMLResponse=';
        return $msg . urlencode($xml);
    }

    protected function addRelayStateToUrl(string &$msg, SamlMessage $message): void
    {
        if (null !== $message->getRelayState()) {
            $msg .= '&RelayState=' . urlencode($message->getRelayState());
        }
    }

    protected function addSignatureToUrl(string &$msg, ?SignatureWriter $signature = null): void
    {
        $key = $signature instanceof SignatureWriter ? $signature->getXmlSecurityKey() : null;

        if (null != $key) {
            $msg .= '&SigAlg=' . urlencode($key->type);
            $signature = $key->signData($msg);
            $msg .= '&Signature=' . urlencode(base64_encode($signature));
        }
    }

    protected function getDestinationUrl(string $msg, SamlMessage $message, ?string $destination): string
    {
        $destination = $message->getDestination() ?: $destination;
        if (!str_contains($destination, '?')) {
            $destination .= '?' . $msg;
        } else {
            $destination .= '&' . $msg;
        }

        return $destination;
    }

    /** @return array<string, string> */
    protected function parseQuery(ServerRequestInterface $request): array
    {
        /*
         * Parse the query string. We need to do this ourself, so that we get access
         * to the raw (urlencoded) values. This is required because different software
         * can urlencode to different values.
         */
        $sigQuery = $relayState = $sigAlg = '';
        $data = $this->parseQueryString($request->getUri()->getQuery());
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

    /** @return array<string, string> */
    protected function parseQueryString(string $queryString): array
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
