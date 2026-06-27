<?php

namespace LightSaml\Binding;

use LightSaml\Context\Profile\Helper\MessageContextHelper;
use LightSaml\Context\Profile\MessageContext;
use LightSaml\Error\LightSamlBindingException;
use LightSaml\Error\LightSamlMissingFactoryException;
use LightSaml\Model\Protocol\AbstractRequest;
use LightSaml\Model\Protocol\SamlMessage;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

class HttpPostBinding extends AbstractBinding
{
    public function __construct(
        private readonly ?ResponseFactoryInterface $responseFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
    ) {
    }

    public function send(MessageContext $context, ?string $destination = null): SamlPostResponse
    {
        if (!$this->responseFactory instanceof ResponseFactoryInterface || !$this->streamFactory instanceof StreamFactoryInterface) {
            throw new LightSamlMissingFactoryException('ResponseFactory and StreamFactory must be provided to use send()');
        }

        $message = MessageContextHelper::asSamlMessage($context);
        $destination = $message->getDestination() ?: $destination;

        $serializationContext = $context->getSerializationContext();
        $message->serialize($serializationContext->getDocument(), $serializationContext);
        $msgStr = $serializationContext->getDocument()->saveXML();

        $this->dispatchSend($msgStr);

        $msgStr = base64_encode($msgStr);

        $type = $message instanceof AbstractRequest ? 'SAMLRequest' : 'SAMLResponse';

        $data = [$type => $msgStr];
        if ($message->getRelayState()) {
            $data['RelayState'] = $message->getRelayState();
        }

        $html = SamlPostResponse::buildHtml($destination, $data);
        $inner = $this->responseFactory->createResponse(200)
            ->withBody($this->streamFactory->createStream($html))
            ->withHeader('Content-Type', 'text/html; charset=utf-8');

        return new SamlPostResponse($inner, $destination, $data);
    }

    public function receive(ServerRequestInterface $request, MessageContext $context): SamlMessage
    {
        $post = (array) ($request->getParsedBody() ?? []);
        if (array_key_exists('SAMLRequest', $post)) {
            $msg = $post['SAMLRequest'];
        } elseif (array_key_exists('SAMLResponse', $post)) {
            $msg = $post['SAMLResponse'];
        } else {
            throw new LightSamlBindingException('Missing SAMLRequest or SAMLResponse parameter');
        }

        $msg = base64_decode($msg, true);

        $msg_decoded = @gzinflate($msg);
        if ($msg_decoded === false) {
            $msg_decoded = $msg;
        }

        $this->dispatchReceive($msg_decoded);

        $deserializationContext = $context->getDeserializationContext();
        $result = SamlMessage::fromXML($msg_decoded, $deserializationContext);

        if (array_key_exists('RelayState', $post)) {
            $result->setRelayState($post['RelayState']);
        }

        $context->setMessage($result);

        return $result;
    }
}
