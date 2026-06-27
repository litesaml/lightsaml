<?php

namespace Tests\Functional\Binding;

use DOMDocument;
use LightSaml\Binding\HttpRedirectBinding;
use LightSaml\Context\Profile\MessageContext;
use LightSaml\Credential\KeyHelper;
use LightSaml\Credential\X509Certificate;
use LightSaml\Event\MessageReceived;
use LightSaml\Event\MessageSent;
use LightSaml\Model\Context\DeserializationContext;
use LightSaml\Model\Protocol\AuthnRequest;
use LightSaml\Model\XmlDSig\AbstractSignatureReader;
use LightSaml\Model\XmlDSig\SignatureStringReader;
use LightSaml\Model\XmlDSig\SignatureWriter;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Tests\BaseTestCase;

class HttpRedirectBindingFunctionalTest extends BaseTestCase
{
    public function test__send_authn_request(): void
    {
        $expectedRelayState = 'relayState';
        $expectedDestination = 'https://destination.com/auth';

        $request = $this->getAuthnRequest();
        $request->setRelayState($expectedRelayState);
        $request->setDestination($expectedDestination);

        $psr17 = new Psr17Factory();
        $biding = new HttpRedirectBinding($psr17);

        $eventDispatcherMock = $this->getEventDispatcherMock();
        $eventDispatcherMock->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function (MessageSent $event): MessageSent {
                $this->assertNotEmpty($event->message);
                $doc = new DOMDocument();
                $doc->loadXML($event->message);
                $this->assertEquals('AuthnRequest', $doc->firstChild->localName);
                return $event;
            });

        $biding->setEventDispatcher($eventDispatcherMock);
        $this->assertSame($eventDispatcherMock, $biding->getEventDispatcher());

        $messageContext = new MessageContext();
        $messageContext->setMessage($request);

        /** @var ResponseInterface $response */
        $response = $biding->send($messageContext);

        $this->assertSame(302, $response->getStatusCode());

        $url = $response->getHeaderLine('Location');
        $this->assertNotEmpty($url);

        $urlInfo = parse_url($url);

        $this->assertEquals($expectedDestination, $urlInfo['scheme'] . '://' . $urlInfo['host'] . $urlInfo['path']);

        $query = [];
        parse_str($urlInfo['query'], $query);

        $this->assertArrayHasKey('SAMLRequest', $query);
        $this->assertArrayHasKey('RelayState', $query);
        $this->assertArrayHasKey('SigAlg', $query);
        $this->assertArrayHasKey('Signature', $query);

        $this->assertEquals(
            'RY/NCsIwEITvPkXI3TaptY3BKkIvBb2oePAiMUmxYBPtbsXHdxFEGBgY5tuf5frd39nLD9DFUHGZCL5eTZabEW9h75+jB2TUCFDxcQg6GuhAB9N70Gj1YbPb6iwR+jFEjDbeOWvqil+Us7ZYqHlbuEU7IxfXq8vnReZblSvfzowvlVOlKzk7/XbTHMIBRt8EQBOQIiHzqZCko8y0EKQzZzUd1QWDX+qG+ACdpu4fJjb2qaEPeLqafAA=',
            $query['SAMLRequest']
        );
        $this->assertEquals($expectedRelayState, $query['RelayState']);
        $this->assertEquals('http://www.w3.org/2001/04/xmldsig-more#rsa-sha256', $query['SigAlg']);
        $this->assertEquals(
            'ACYzxh5RjdIT+qz5oDgksPLSqUhKok5HykqjvNencaoppaAmHvclM5Wp98B/6Pym2rqjxzqaUQalI5+1X5lSvpQLp4GxfBDMWftRjVg+LFd0LsgdAVRtpVfeLtbX3BDhKN7abpyETXLvYMNciKOt8v7aFLKRzNC/2jhGlctOMubC2LtEKcAK5eO2vbV7bdSsQLTQFS3949bQrfVr0Mr+7E2eytWW17U/sUt0W6WPeiB4TPQLfPtYN/tSn0982XHyS/Y8CNBrX1iMDg5/35DlWZR/2NNzCB1IJImqOx/TF9v1IGvOVlcTzPaCeoVBbdAAaRLlFdFqvawjsCd1uju83g==',
            $query['Signature']
        );

        $xml = gzinflate(base64_decode($query['SAMLRequest'], true));

        $context = new DeserializationContext();
        $context->getDocument()->loadXML($xml);

        $receivedAuthnRequest = new AuthnRequest();
        $receivedAuthnRequest->deserialize($context->getDocument(), $context);

        $this->assertEquals($request->getID(), $receivedAuthnRequest->getID());
        $this->assertEquals($request->getIssueInstantTimestamp(), $receivedAuthnRequest->getIssueInstantTimestamp());
    }

    public function test__send_destination(): void
    {
        $expectedDestination = 'https://destination.com/auth';

        $request = $this->getAuthnRequest();

        $psr17 = new Psr17Factory();
        $biding = new HttpRedirectBinding($psr17);

        $messageContext = new MessageContext();
        $messageContext->setMessage($request);

        /** @var ResponseInterface $response */
        $response = $biding->send($messageContext, $expectedDestination);

        $this->assertSame(302, $response->getStatusCode());

        $url = $response->getHeaderLine('Location');
        $this->assertNotEmpty($url);

        $urlInfo = parse_url($url);

        $this->assertEquals($expectedDestination, $urlInfo['scheme'] . '://' . $urlInfo['host'] . $urlInfo['path']);
    }

    public function test__receive_authn_request(): void
    {
        $expectedRelayState = 'relayState';

        $psr17 = new Psr17Factory();
        $binding = new HttpRedirectBinding($psr17);

        $eventDispatcherMock = $this->getEventDispatcherMock();
        $eventDispatcherMock->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function (MessageReceived $event): MessageReceived {
                $this->assertNotEmpty($event->message);
                $doc = new DOMDocument();
                $doc->loadXML($event->message);
                $this->assertEquals('AuthnRequest', $doc->firstChild->localName);
                return $event;
            });

        $binding->setEventDispatcher($eventDispatcherMock);
        $this->assertSame($eventDispatcherMock, $binding->getEventDispatcher());

        $rawQuery = 'SAMLRequest=' . urlencode('RY/NCsIwEITvPkXI3TaptY3BKkIvBb2oePAiMUmxYBPtbsXHdxFEGBgY5tuf5frd39nLD9DFUHGZCL5eTZabEW9h75+jB2TUCFDxcQg6GuhAB9N70Gj1YbPb6iwR+jFEjDbeOWvqil+Us7ZYqHlbuEU7IxfXq8vnReZblSvfzowvlVOlKzk7/XbTHMIBRt8EQBOQIiHzqZCko8y0EKQzZzUd1QWDX+qG+ACdpu4fJjb2qaEPeLqafAA=')
            . '&RelayState=' . urlencode($expectedRelayState)
            . '&SigAlg=' . urlencode('http://www.w3.org/2000/09/xmldsig#rsa-sha1')
            . '&Signature=' . urlencode('SI4nZH+9tjLO24k2La/v5DJ/OfGWw/nKKc/Nh8ih/AN71HuIzFl30F3Va+pDOidRYgJ8dIB2Juf5DIQYggDz+AiR/NI9gkAIGKRYZ3bhBPzC0XVtTQ075Qxwa3HWimh2Lywj7WV0QANOptodnjp1aUf4SuSHfEYrcWTf5C0gOZhiXT7XIQH0wpL1BdLwaePlduVCfaaMq2iNadNFBHi2+d9+FrCHyxYdmR8r5CbNg1vNEHj1xYwWUMBEtvJIYAt116++ei78dQYKlv5Mz98pTB1bkjRtONh+w7Mdy1gGT+D/gDz1kl+kAfxIT6D2x54GFBKM01gAGRUrb0Z6j2Nn6Q==');

        $request = $psr17->createServerRequest('GET', '/?' . $rawQuery);

        $messageContext = new MessageContext();
        $binding->receive($request, $messageContext);
        /** @var AuthnRequest $message */
        $message = $messageContext->getMessage();

        $this->assertInstanceOf(AuthnRequest::class, $message);
        $this->assertEquals($expectedRelayState, $message->getRelayState());
        $this->assertEquals('_8dcc6985f6d9f385f0bbd4562ef848ef3ae78d87d7', $message->getID());
        $this->assertEquals('2014-01-01T12:00:00Z', $message->getIssueInstantString());
        $this->assertNotNull($message->getSignature());
        $this->assertInstanceOf(AbstractSignatureReader::class, $message->getSignature());
        $this->assertInstanceOf(SignatureStringReader::class, $message->getSignature());

        /** @var SignatureStringReader $signature */
        $signature = $message->getSignature();
        $this->assertEquals('http://www.w3.org/2000/09/xmldsig#rsa-sha1', $signature->getAlgorithm());
        $this->assertEquals(
            'SI4nZH+9tjLO24k2La/v5DJ/OfGWw/nKKc/Nh8ih/AN71HuIzFl30F3Va+pDOidRYgJ8dIB2Juf5DIQYggDz+AiR/NI9gkAIGKRYZ3bhBPzC0XVtTQ075Qxwa3HWimh2Lywj7WV0QANOptodnjp1aUf4SuSHfEYrcWTf5C0gOZhiXT7XIQH0wpL1BdLwaePlduVCfaaMq2iNadNFBHi2+d9+FrCHyxYdmR8r5CbNg1vNEHj1xYwWUMBEtvJIYAt116++ei78dQYKlv5Mz98pTB1bkjRtONh+w7Mdy1gGT+D/gDz1kl+kAfxIT6D2x54GFBKM01gAGRUrb0Z6j2Nn6Q==',
            $signature->getSignature()
        );
    }

    private function getAuthnRequest(): AuthnRequest
    {
        $authnRequest = new AuthnRequest();
        $authnRequest->setIssueInstant('2014-01-01T12:00:00Z');
        $authnRequest->setID('_8dcc6985f6d9f385f0bbd4562ef848ef3ae78d87d7');

        $certificate = new X509Certificate();
        $certificate->loadFromFile(__DIR__ . '/../../resources/saml.crt');

        $key = KeyHelper::createPrivateKey(__DIR__ . '/../../resources/saml.pem', '', true);

        $authnRequest->setSignature(new SignatureWriter($certificate, $key));

        return $authnRequest;
    }

    /**
     * @return MockObject|EventDispatcherInterface
     */
    private function getEventDispatcherMock(): MockObject
    {
        return $this->getMockBuilder(EventDispatcherInterface::class)->getMock();
    }
}
