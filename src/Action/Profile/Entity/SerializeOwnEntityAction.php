<?php

namespace LightSaml\Action\Profile\Entity;

use LightSaml\Action\Profile\AbstractProfileAction;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Context\Profile\ProfileContexts;
use LightSaml\Model\Context\SerializationContext;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;

class SerializeOwnEntityAction extends AbstractProfileAction
{
    /** @var string[] */
    protected $supportedContextTypes = ['application/samlmetadata+xml', 'application/xml', 'text/xml'];

    public function __construct(
        LoggerInterface $logger,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface $streamFactory,
    ) {
        parent::__construct($logger);
    }

    protected function doExecute(ProfileContext $context)
    {
        $ownEntityDescriptor = $context->getOwnEntityDescriptor();

        /** @var SerializationContext $serializationContext */
        $serializationContext = $context->getSubContext(ProfileContexts::SERIALIZATION, SerializationContext::class);
        $serializationContext->getDocument()->formatOutput = true;

        $ownEntityDescriptor->serialize($serializationContext->getDocument(), $serializationContext);

        $xml = $serializationContext->getDocument()->saveXML();

        $contentType = 'text/xml';
        $acceptHeader = $context->getHttpRequest()->getHeaderLine('Accept');
        $acceptParts = array_map('trim', explode(',', $acceptHeader));
        $acceptableTypes = array_map(fn($part) => trim(explode(';', $part)[0]), $acceptParts);
        foreach ($this->supportedContextTypes as $supportedContentType) {
            if (in_array($supportedContentType, $acceptableTypes, true)) {
                $contentType = $supportedContentType;
                break;
            }
        }

        $response = $this->responseFactory->createResponse(200)
            ->withBody($this->streamFactory->createStream($xml))
            ->withHeader('Content-Type', $contentType);

        $context->getHttpResponseContext()->setResponse($response);
    }
}
