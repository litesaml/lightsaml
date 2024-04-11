<?php

namespace LightSaml\Tests\Functional\Binding;

use LightSaml\Binding\HttpPostBinding;
use LightSaml\Context\Profile\MessageContext;
use LightSaml\Event\MessageReceived;
use LightSaml\Event\MessageSent;
use LightSaml\Model\Protocol\AuthnRequest;
use LightSaml\Model\XmlDSig\SignatureWriter;
use LightSaml\Credential\KeyHelper;
use LightSaml\Credential\X509Certificate;
use LightSaml\Tests\BaseTestCase;
use Symfony\Component\HttpFoundation\Request;

class HttpPostBindingFunctionalTest extends BaseTestCase
{
    public function test_send_authn_request()
    {
        $expectedRelayState = 'relayState';
        $expectedDestination = 'https://destination.com/auth';

        $request = $this->getAuthnRequest();
        $request->setRelayState($expectedRelayState);
        $request->setDestination($expectedDestination);

        $biding = new HttpPostBinding();

        $eventDispatcherMock = $this->getEventDispatcherMock();
        $eventDispatcherMock->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function (MessageSent $event) {
                $this->assertNotEmpty($event->message);
                $doc = new \DOMDocument();
                $doc->loadXML($event->message);
                $this->assertEquals('AuthnRequest', $doc->firstChild->localName);
                return $event;
            });

        $biding->setEventDispatcher($eventDispatcherMock);
        $this->assertSame($eventDispatcherMock, $biding->getEventDispatcher());

        $messageContext = new MessageContext();
        $messageContext->setMessage($request);

        /** @var \LightSaml\Binding\SamlPostResponse $response */
        $response = $biding->send($messageContext);

        $this->assertInstanceOf('LightSaml\Binding\SamlPostResponse', $response);

        $data = $response->getData();

        // RelayState
        // SAMLRequest PD94bWwgdmVyc2lvbj0iMS4wIj8+CjxBdXRoblJlcXVlc3QgeG1sbnM9InVybjpvYXNpczpuYW1lczp0YzpTQU1MOjIuMDpwcm90b2NvbCIgSUQ9Il84ZGNjNjk4NWY2ZDlmMzg1ZjBiYmQ0NTYyZWY4NDhlZjNhZTc4ZDg3ZDciIFZlcnNpb249IjIuMCIgSXNzdWVJbnN0YW50PSIyMDE0LTAxLTAxVDEyOjAwOjAwWiIgRGVzdGluYXRpb249Imh0dHBzOi8vZGVzdGluYXRpb24uY29tL2F1dGgiPjxkczpTaWduYXR1cmUgeG1sbnM6ZHM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvMDkveG1sZHNpZyMiPgogIDxkczpTaWduZWRJbmZvPjxkczpDYW5vbmljYWxpemF0aW9uTWV0aG9kIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvMjAwMS8xMC94bWwtZXhjLWMxNG4jIi8+CiAgICA8ZHM6U2lnbmF0dXJlTWV0aG9kIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvMjAwMC8wOS94bWxkc2lnI3JzYS1zaGExIi8+CiAgPGRzOlJlZmVyZW5jZSBVUkk9IiNfOGRjYzY5ODVmNmQ5ZjM4NWYwYmJkNDU2MmVmODQ4ZWYzYWU3OGQ4N2Q3Ij48ZHM6VHJhbnNmb3Jtcz48ZHM6VHJhbnNmb3JtIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvMjAwMC8wOS94bWxkc2lnI2VudmVsb3BlZC1zaWduYXR1cmUiLz48ZHM6VHJhbnNmb3JtIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvMjAwMS8xMC94bWwtZXhjLWMxNG4jIi8+PC9kczpUcmFuc2Zvcm1zPjxkczpEaWdlc3RNZXRob2QgQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwLzA5L3htbGRzaWcjc2hhMSIvPjxkczpEaWdlc3RWYWx1ZT4xZkNSemxSblVPWjJ1V1o3VVAwNFlDTEMyQW89PC9kczpEaWdlc3RWYWx1ZT48L2RzOlJlZmVyZW5jZT48L2RzOlNpZ25lZEluZm8+PGRzOlNpZ25hdHVyZVZhbHVlPlIwbkliSXpxSHNFWkp6MGhmOUp2OHdRb0hRcWJrZzhVc3Z5aE9GNVR3d1hxVjZsNlZGaWJYR1Y1U0RncVMxcHhGN2plL3F4SnFNc0J4RkR2L0M4QS96cWx0UzBFMVo5Vkh0NXR5SDRZNms0c3FoN0MxTFdudmNPRHRoS1RRT2NsaytKUW9SWCtMQmJLQ1dKdktpbEZHbHRYdkpKdlZXZTg2QzV0cUdkU0d4Z1QrdGhvZkNLR0h6YzBwL1FiMzFlbWduT1QyK0xHb1E2K2F3Y09IWS9QektNa3RWSmgxb1NEWXVmVkRpczhyeXNYWTZ1T3dzeU5BUUhiL2tvQ3FTSXFtWk9jQ3RUamdPWlNOZFl2Mm5sakhQTENBSmtjQk5nM3JSU2NEMXJwWFZNV1FwVWlzV3pUb3V0RzhMbUNxOHRFVU5tdW90N2tISjA4dFhEbEUzMHVEUT09PC9kczpTaWduYXR1cmVWYWx1ZT4KPGRzOktleUluZm8+PGRzOlg1MDlEYXRhPjxkczpYNTA5Q2VydGlmaWNhdGU+TUlJRHJEQ0NBcFNnQXdJQkFnSUpBSXh6YkdMb3UzQmpNQTBHQ1NxR1NJYjNEUUVCQlFVQU1FSXhDekFKQmdOVkJBWVRBbEpUTVE4d0RRWURWUVFJRXdaVFpYSmlhV0V4RERBS0JnTlZCQW9UQTBKUFV6RVVNQklHQTFVRUF4TUxiWFF1WlhadkxuUmxZVzB3SGhjTk1UTXhNREE0TVRnMU9UTXlXaGNOTWpNeE1EQTRNVGcxT1RNeVdqQkNNUXN3Q1FZRFZRUUdFd0pTVXpFUE1BMEdBMVVFQ0JNR1UyVnlZbWxoTVF3d0NnWURWUVFLRXdOQ1QxTXhGREFTQmdOVkJBTVRDMjEwTG1WMmJ5NTBaV0Z0TUlJQklqQU5CZ2txaGtpRzl3MEJBUUVGQUFPQ0FROEFNSUlCQ2dLQ0FRRUF3czdqTUw0N2pUUWJXbGVSd2loazE1d09qdXNwb0tQY3hXMWFFUmV4QU1XZThCTXMxTWVlVE9NWGpuQTM1YnJlR2E5UHdKaTJLanREejNna2hWQ2dsWnpMWkdCTExPN3VjaFp2YWdGaFRvbVphMjBqVHFPNkpRYkRsaTNwWU5QMGZCSXJtRWJIOWNmaGdtOTFGbSs2YlRWbko0eFFoVDRhUFdyUEFWS1UyRkRUQkZCZjRRTk1JYjFpSTFvTkVydDNpb2NzYlJUYkl5amp2SWU4eUxWcnRtWlhBMERua3hCL3JpeW0wR1QrNGdwT0VLVjZHVU1URjF4MGVRTVV6dzRka3hoRnM3ZnY2WXJKeW10RU1tSE9laUE1dlZQRXR4RXI4NEpBWEp5WlVhWmZ1ZmtqL2pIVWxYK1BPRld4MkpSdis0MjhnaHJYcE52cVVOcXY3b3pmRndJREFRQUJvNEdrTUlHaE1CMEdBMVVkRGdRV0JCUm9tZjNYeWM1Y2szY2VJWHEwbjQ1cHhVa2d3akJ5QmdOVkhTTUVhekJwZ0JSb21mM1h5YzVjazNjZUlYcTBuNDVweFVrZ3dxRkdwRVF3UWpFTE1Ba0dBMVVFQmhNQ1VsTXhEekFOQmdOVkJBZ1RCbE5sY21KcFlURU1NQW9HQTFVRUNoTURRazlUTVJRd0VnWURWUVFERXd0dGRDNWxkbTh1ZEdWaGJZSUpBSXh6YkdMb3UzQmpNQXdHQTFVZEV3UUZNQU1CQWY4d0RRWUpLb1pJaHZjTkFRRUZCUUFEZ2dFQkFHQVhjOHBlNis2b3dsOXoyaXF5YkU2cGJqWFRLcWpTY2xNR3JkZW9vSXRVMXhHcUJoWXUvYjJxNmhFdllaQ3pscVllNWV1ZjNyOEM3R0FBS0VZeXV3dTN4dUxEWVY0bjZsNmVXVElsMWRvdWcrcjBCbDhaMzE1N0E0QmNnbVVUNjRRa2VrSTJWREhPOFdBZERPV1FnMVVURW9xQ3J5VE90bVJhQzM5MWlHQXFiejF3dFp0Vjk1Ym9HZHVyOFNDaEs5TEtjUHJiQ0R4cG82NEJNZ3RQazJIa1JnRTdoNVlXa0xIeG14d1pyWWkzRUFmUzZJdWNibFkzd3dZNEdFaXg4RFFoMWxZZ3B2NVRPRDhJTVZmK29VV2RwODFVbi9JcUhxTGhuU3Vwd2s2ckJZYlVGaE4vQ2xLNVVjb0RxV0hjajI3dEdLRDZhTmx4VGRTd2NZQmwzVHM9PC9kczpYNTA5Q2VydGlmaWNhdGU+PC9kczpYNTA5RGF0YT48L2RzOktleUluZm8+PC9kczpTaWduYXR1cmU+PC9BdXRoblJlcXVlc3Q+Cg==

        $this->assertArrayHasKey('SAMLRequest', $data);
        $this->assertArrayHasKey('RelayState', $data);
        $this->assertEquals(
            'PD94bWwgdmVyc2lvbj0iMS4wIj8+CjxBdXRoblJlcXVlc3QgeG1sbnM9InVybjpvYXNpczpuYW1lczp0YzpTQU1MOjIuMDpwcm90b2NvbCIgSUQ9Il84ZGNjNjk4NWY2ZDlmMzg1ZjBiYmQ0NTYyZWY4NDhlZjNhZTc4ZDg3ZDciIFZlcnNpb249IjIuMCIgSXNzdWVJbnN0YW50PSIyMDE0LTAxLTAxVDEyOjAwOjAwWiIgRGVzdGluYXRpb249Imh0dHBzOi8vZGVzdGluYXRpb24uY29tL2F1dGgiPjxkczpTaWduYXR1cmUgeG1sbnM6ZHM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvMDkveG1sZHNpZyMiPgogIDxkczpTaWduZWRJbmZvPjxkczpDYW5vbmljYWxpemF0aW9uTWV0aG9kIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvMjAwMS8xMC94bWwtZXhjLWMxNG4jIi8+CiAgICA8ZHM6U2lnbmF0dXJlTWV0aG9kIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvMjAwMC8wOS94bWxkc2lnI3JzYS1zaGExIi8+CiAgPGRzOlJlZmVyZW5jZSBVUkk9IiNfOGRjYzY5ODVmNmQ5ZjM4NWYwYmJkNDU2MmVmODQ4ZWYzYWU3OGQ4N2Q3Ij48ZHM6VHJhbnNmb3Jtcz48ZHM6VHJhbnNmb3JtIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvMjAwMC8wOS94bWxkc2lnI2VudmVsb3BlZC1zaWduYXR1cmUiLz48ZHM6VHJhbnNmb3JtIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvMjAwMS8xMC94bWwtZXhjLWMxNG4jIi8+PC9kczpUcmFuc2Zvcm1zPjxkczpEaWdlc3RNZXRob2QgQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwLzA5L3htbGRzaWcjc2hhMSIvPjxkczpEaWdlc3RWYWx1ZT4xZkNSemxSblVPWjJ1V1o3VVAwNFlDTEMyQW89PC9kczpEaWdlc3RWYWx1ZT48L2RzOlJlZmVyZW5jZT48L2RzOlNpZ25lZEluZm8+PGRzOlNpZ25hdHVyZVZhbHVlPlIwbkliSXpxSHNFWkp6MGhmOUp2OHdRb0hRcWJrZzhVc3Z5aE9GNVR3d1hxVjZsNlZGaWJYR1Y1U0RncVMxcHhGN2plL3F4SnFNc0J4RkR2L0M4QS96cWx0UzBFMVo5Vkh0NXR5SDRZNms0c3FoN0MxTFdudmNPRHRoS1RRT2NsaytKUW9SWCtMQmJLQ1dKdktpbEZHbHRYdkpKdlZXZTg2QzV0cUdkU0d4Z1QrdGhvZkNLR0h6YzBwL1FiMzFlbWduT1QyK0xHb1E2K2F3Y09IWS9QektNa3RWSmgxb1NEWXVmVkRpczhyeXNYWTZ1T3dzeU5BUUhiL2tvQ3FTSXFtWk9jQ3RUamdPWlNOZFl2Mm5sakhQTENBSmtjQk5nM3JSU2NEMXJwWFZNV1FwVWlzV3pUb3V0RzhMbUNxOHRFVU5tdW90N2tISjA4dFhEbEUzMHVEUT09PC9kczpTaWduYXR1cmVWYWx1ZT4KPGRzOktleUluZm8+PGRzOlg1MDlEYXRhPjxkczpYNTA5Q2VydGlmaWNhdGU+TUlJRHJEQ0NBcFNnQXdJQkFnSUpBSXh6YkdMb3UzQmpNQTBHQ1NxR1NJYjNEUUVCQlFVQU1FSXhDekFKQmdOVkJBWVRBbEpUTVE4d0RRWURWUVFJRXdaVFpYSmlhV0V4RERBS0JnTlZCQW9UQTBKUFV6RVVNQklHQTFVRUF4TUxiWFF1WlhadkxuUmxZVzB3SGhjTk1UTXhNREE0TVRnMU9UTXlXaGNOTWpNeE1EQTRNVGcxT1RNeVdqQkNNUXN3Q1FZRFZRUUdFd0pTVXpFUE1BMEdBMVVFQ0JNR1UyVnlZbWxoTVF3d0NnWURWUVFLRXdOQ1QxTXhGREFTQmdOVkJBTVRDMjEwTG1WMmJ5NTBaV0Z0TUlJQklqQU5CZ2txaGtpRzl3MEJBUUVGQUFPQ0FROEFNSUlCQ2dLQ0FRRUF3czdqTUw0N2pUUWJXbGVSd2loazE1d09qdXNwb0tQY3hXMWFFUmV4QU1XZThCTXMxTWVlVE9NWGpuQTM1YnJlR2E5UHdKaTJLanREejNna2hWQ2dsWnpMWkdCTExPN3VjaFp2YWdGaFRvbVphMjBqVHFPNkpRYkRsaTNwWU5QMGZCSXJtRWJIOWNmaGdtOTFGbSs2YlRWbko0eFFoVDRhUFdyUEFWS1UyRkRUQkZCZjRRTk1JYjFpSTFvTkVydDNpb2NzYlJUYkl5amp2SWU4eUxWcnRtWlhBMERua3hCL3JpeW0wR1QrNGdwT0VLVjZHVU1URjF4MGVRTVV6dzRka3hoRnM3ZnY2WXJKeW10RU1tSE9laUE1dlZQRXR4RXI4NEpBWEp5WlVhWmZ1ZmtqL2pIVWxYK1BPRld4MkpSdis0MjhnaHJYcE52cVVOcXY3b3pmRndJREFRQUJvNEdrTUlHaE1CMEdBMVVkRGdRV0JCUm9tZjNYeWM1Y2szY2VJWHEwbjQ1cHhVa2d3akJ5QmdOVkhTTUVhekJwZ0JSb21mM1h5YzVjazNjZUlYcTBuNDVweFVrZ3dxRkdwRVF3UWpFTE1Ba0dBMVVFQmhNQ1VsTXhEekFOQmdOVkJBZ1RCbE5sY21KcFlURU1NQW9HQTFVRUNoTURRazlUTVJRd0VnWURWUVFERXd0dGRDNWxkbTh1ZEdWaGJZSUpBSXh6YkdMb3UzQmpNQXdHQTFVZEV3UUZNQU1CQWY4d0RRWUpLb1pJaHZjTkFRRUZCUUFEZ2dFQkFHQVhjOHBlNis2b3dsOXoyaXF5YkU2cGJqWFRLcWpTY2xNR3JkZW9vSXRVMXhHcUJoWXUvYjJxNmhFdllaQ3pscVllNWV1ZjNyOEM3R0FBS0VZeXV3dTN4dUxEWVY0bjZsNmVXVElsMWRvdWcrcjBCbDhaMzE1N0E0QmNnbVVUNjRRa2VrSTJWREhPOFdBZERPV1FnMVVURW9xQ3J5VE90bVJhQzM5MWlHQXFiejF3dFp0Vjk1Ym9HZHVyOFNDaEs5TEtjUHJiQ0R4cG82NEJNZ3RQazJIa1JnRTdoNVlXa0xIeG14d1pyWWkzRUFmUzZJdWNibFkzd3dZNEdFaXg4RFFoMWxZZ3B2NVRPRDhJTVZmK29VV2RwODFVbi9JcUhxTGhuU3Vwd2s2ckJZYlVGaE4vQ2xLNVVjb0RxV0hjajI3dEdLRDZhTmx4VGRTd2NZQmwzVHM9PC9kczpYNTA5Q2VydGlmaWNhdGU+PC9kczpYNTA5RGF0YT48L2RzOktleUluZm8+PC9kczpTaWduYXR1cmU+PC9BdXRoblJlcXVlc3Q+Cg==',
            $data['SAMLRequest']
        );
        $this->assertEquals($expectedRelayState, $data['RelayState']);

        $this->assertEquals($expectedDestination, $response->getDestination());
    }

    public function test_send_destination()
    {
        $expectedDestination = 'https://destination.com/auth';

        $request = $this->getAuthnRequest();

        $biding = new HttpPostBinding();

        $messageContext = new MessageContext();
        $messageContext->setMessage($request);

        /** @var \LightSaml\Binding\SamlPostResponse $response */
        $response = $biding->send($messageContext, $expectedDestination);

        $this->assertInstanceOf('LightSaml\Binding\SamlPostResponse', $response);

        $this->assertEquals($expectedDestination, $response->getDestination());
    }

    public function test_receive_authn_request_uncompressed()
    {
        $expectedRelayState = 'relayState';

        $request = new Request();
        $request->setMethod('POST');
        $request->request->add(array(
            'SAMLRequest' => 'PD94bWwgdmVyc2lvbj0iMS4wIj8+CjxBdXRoblJlcXVlc3QgeG1sbnM9InVybjpvYXNpczpuYW1lczp0YzpTQU1MOjIuMDpwcm90b2NvbCIgSUQ9Il84ZGNjNjk4NWY2ZDlmMzg1ZjBiYmQ0NTYyZWY4NDhlZjNhZTc4ZDg3ZDciIFZlcnNpb249IjIuMCIgSXNzdWVJbnN0YW50PSIyMDE0LTAxLTAxVDEyOjAwOjAwWiIgRGVzdGluYXRpb249Imh0dHBzOi8vZGVzdGluYXRpb24uY29tL2F1dGgiPjxkczpTaWduYXR1cmUgeG1sbnM6ZHM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvMDkveG1sZHNpZyMiPgogIDxkczpTaWduZWRJbmZvPjxkczpDYW5vbmljYWxpemF0aW9uTWV0aG9kIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvMjAwMS8xMC94bWwtZXhjLWMxNG4jIi8+CiAgICA8ZHM6U2lnbmF0dXJlTWV0aG9kIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvMjAwMC8wOS94bWxkc2lnI3JzYS1zaGExIi8+CiAgPGRzOlJlZmVyZW5jZSBVUkk9IiNfOGRjYzY5ODVmNmQ5ZjM4NWYwYmJkNDU2MmVmODQ4ZWYzYWU3OGQ4N2Q3Ij48ZHM6VHJhbnNmb3Jtcz48ZHM6VHJhbnNmb3JtIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvMjAwMC8wOS94bWxkc2lnI2VudmVsb3BlZC1zaWduYXR1cmUiLz48ZHM6VHJhbnNmb3JtIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvMjAwMS8xMC94bWwtZXhjLWMxNG4jIi8+PC9kczpUcmFuc2Zvcm1zPjxkczpEaWdlc3RNZXRob2QgQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwLzA5L3htbGRzaWcjc2hhMSIvPjxkczpEaWdlc3RWYWx1ZT4xZkNSemxSblVPWjJ1V1o3VVAwNFlDTEMyQW89PC9kczpEaWdlc3RWYWx1ZT48L2RzOlJlZmVyZW5jZT48L2RzOlNpZ25lZEluZm8+PGRzOlNpZ25hdHVyZVZhbHVlPlIwbkliSXpxSHNFWkp6MGhmOUp2OHdRb0hRcWJrZzhVc3Z5aE9GNVR3d1hxVjZsNlZGaWJYR1Y1U0RncVMxcHhGN2plL3F4SnFNc0J4RkR2L0M4QS96cWx0UzBFMVo5Vkh0NXR5SDRZNms0c3FoN0MxTFdudmNPRHRoS1RRT2NsaytKUW9SWCtMQmJLQ1dKdktpbEZHbHRYdkpKdlZXZTg2QzV0cUdkU0d4Z1QrdGhvZkNLR0h6YzBwL1FiMzFlbWduT1QyK0xHb1E2K2F3Y09IWS9QektNa3RWSmgxb1NEWXVmVkRpczhyeXNYWTZ1T3dzeU5BUUhiL2tvQ3FTSXFtWk9jQ3RUamdPWlNOZFl2Mm5sakhQTENBSmtjQk5nM3JSU2NEMXJwWFZNV1FwVWlzV3pUb3V0RzhMbUNxOHRFVU5tdW90N2tISjA4dFhEbEUzMHVEUT09PC9kczpTaWduYXR1cmVWYWx1ZT4KPGRzOktleUluZm8+PGRzOlg1MDlEYXRhPjxkczpYNTA5Q2VydGlmaWNhdGU+TUlJRHJEQ0NBcFNnQXdJQkFnSUpBSXh6YkdMb3UzQmpNQTBHQ1NxR1NJYjNEUUVCQlFVQU1FSXhDekFKQmdOVkJBWVRBbEpUTVE4d0RRWURWUVFJRXdaVFpYSmlhV0V4RERBS0JnTlZCQW9UQTBKUFV6RVVNQklHQTFVRUF4TUxiWFF1WlhadkxuUmxZVzB3SGhjTk1UTXhNREE0TVRnMU9UTXlXaGNOTWpNeE1EQTRNVGcxT1RNeVdqQkNNUXN3Q1FZRFZRUUdFd0pTVXpFUE1BMEdBMVVFQ0JNR1UyVnlZbWxoTVF3d0NnWURWUVFLRXdOQ1QxTXhGREFTQmdOVkJBTVRDMjEwTG1WMmJ5NTBaV0Z0TUlJQklqQU5CZ2txaGtpRzl3MEJBUUVGQUFPQ0FROEFNSUlCQ2dLQ0FRRUF3czdqTUw0N2pUUWJXbGVSd2loazE1d09qdXNwb0tQY3hXMWFFUmV4QU1XZThCTXMxTWVlVE9NWGpuQTM1YnJlR2E5UHdKaTJLanREejNna2hWQ2dsWnpMWkdCTExPN3VjaFp2YWdGaFRvbVphMjBqVHFPNkpRYkRsaTNwWU5QMGZCSXJtRWJIOWNmaGdtOTFGbSs2YlRWbko0eFFoVDRhUFdyUEFWS1UyRkRUQkZCZjRRTk1JYjFpSTFvTkVydDNpb2NzYlJUYkl5amp2SWU4eUxWcnRtWlhBMERua3hCL3JpeW0wR1QrNGdwT0VLVjZHVU1URjF4MGVRTVV6dzRka3hoRnM3ZnY2WXJKeW10RU1tSE9laUE1dlZQRXR4RXI4NEpBWEp5WlVhWmZ1ZmtqL2pIVWxYK1BPRld4MkpSdis0MjhnaHJYcE52cVVOcXY3b3pmRndJREFRQUJvNEdrTUlHaE1CMEdBMVVkRGdRV0JCUm9tZjNYeWM1Y2szY2VJWHEwbjQ1cHhVa2d3akJ5QmdOVkhTTUVhekJwZ0JSb21mM1h5YzVjazNjZUlYcTBuNDVweFVrZ3dxRkdwRVF3UWpFTE1Ba0dBMVVFQmhNQ1VsTXhEekFOQmdOVkJBZ1RCbE5sY21KcFlURU1NQW9HQTFVRUNoTURRazlUTVJRd0VnWURWUVFERXd0dGRDNWxkbTh1ZEdWaGJZSUpBSXh6YkdMb3UzQmpNQXdHQTFVZEV3UUZNQU1CQWY4d0RRWUpLb1pJaHZjTkFRRUZCUUFEZ2dFQkFHQVhjOHBlNis2b3dsOXoyaXF5YkU2cGJqWFRLcWpTY2xNR3JkZW9vSXRVMXhHcUJoWXUvYjJxNmhFdllaQ3pscVllNWV1ZjNyOEM3R0FBS0VZeXV3dTN4dUxEWVY0bjZsNmVXVElsMWRvdWcrcjBCbDhaMzE1N0E0QmNnbVVUNjRRa2VrSTJWREhPOFdBZERPV1FnMVVURW9xQ3J5VE90bVJhQzM5MWlHQXFiejF3dFp0Vjk1Ym9HZHVyOFNDaEs5TEtjUHJiQ0R4cG82NEJNZ3RQazJIa1JnRTdoNVlXa0xIeG14d1pyWWkzRUFmUzZJdWNibFkzd3dZNEdFaXg4RFFoMWxZZ3B2NVRPRDhJTVZmK29VV2RwODFVbi9JcUhxTGhuU3Vwd2s2ckJZYlVGaE4vQ2xLNVVjb0RxV0hjajI3dEdLRDZhTmx4VGRTd2NZQmwzVHM9PC9kczpYNTA5Q2VydGlmaWNhdGU+PC9kczpYNTA5RGF0YT48L2RzOktleUluZm8+PC9kczpTaWduYXR1cmU+PC9BdXRoblJlcXVlc3Q+Cg==',
            'RelayState' => $expectedRelayState,
        ));

        $binding = new HttpPostBinding();

        $eventDispatcherMock = $this->getEventDispatcherMock();
        $eventDispatcherMock->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function (MessageReceived $event) {
                $this->assertNotEmpty($event->message);
                $doc = new \DOMDocument();
                $doc->loadXML($event->message);
                $this->assertEquals('AuthnRequest', $doc->firstChild->localName);
                return $event;
            });

        $binding->setEventDispatcher($eventDispatcherMock);
        $this->assertSame($eventDispatcherMock, $binding->getEventDispatcher());

        $messageContext = new MessageContext();
        $binding->receive($request, $messageContext);
        /** @var \LightSaml\Model\Protocol\AuthnRequest $message */
        $message = $messageContext->getMessage();

        $this->assertInstanceOf('LightSaml\Model\Protocol\AuthnRequest', $message);

        $this->assertEquals($expectedRelayState, $message->getRelayState());

        $this->assertNotNull($message->getSignature());
        $this->assertInstanceOf('LightSaml\Model\XmlDSig\AbstractSignatureReader', $message->getSignature());
        $this->assertInstanceOf('LightSaml\Model\XmlDSig\SignatureXmlReader', $message->getSignature());
    }

    public function test_receive_authn_request_compressed()
    {
        $expectedRelayState = 'relayState';

        $request = new Request();
        $request->setMethod('POST');
        $request->request->add(array(
            'SAMLRequest' => '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',
            'RelayState' => $expectedRelayState,
        ));

        $binding = new HttpPostBinding();

        $eventDispatcherMock = $this->getEventDispatcherMock();
        $eventDispatcherMock->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function (MessageReceived $event) {
                $this->assertNotEmpty($event->message);
                $doc = new \DOMDocument();
                $doc->loadXML($event->message);
                $this->assertEquals('AuthnRequest', $doc->firstChild->localName);
                return $event;
            });

        $binding->setEventDispatcher($eventDispatcherMock);
        $this->assertSame($eventDispatcherMock, $binding->getEventDispatcher());

        $messageContext = new MessageContext();
        $binding->receive($request, $messageContext);
        /** @var \LightSaml\Model\Protocol\AuthnRequest $message */
        $message = $messageContext->getMessage();

        $this->assertInstanceOf('LightSaml\Model\Protocol\AuthnRequest', $message);

        $this->assertEquals($expectedRelayState, $message->getRelayState());

        $this->assertNotNull($message->getSignature());
        $this->assertInstanceOf('LightSaml\Model\XmlDSig\AbstractSignatureReader', $message->getSignature());
        $this->assertInstanceOf('LightSaml\Model\XmlDSig\SignatureXmlReader', $message->getSignature());
    }

    /**
     * @return AuthnRequest
     */
    private function getAuthnRequest()
    {
        $authnRequest = new AuthnRequest();
        $authnRequest->setIssueInstant('2014-01-01T12:00:00Z');
        $authnRequest->setID('_8dcc6985f6d9f385f0bbd4562ef848ef3ae78d87d7');

        $certificate = new X509Certificate();
        $certificate->loadFromFile(__DIR__.'/../../resources/saml.crt');

        $key = KeyHelper::createPrivateKey(__DIR__.'/../../resources/saml.pem', '', true);

        $authnRequest->setSignature(new SignatureWriter($certificate, $key));

        return $authnRequest;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Psr\EventDispatcher\EventDispatcherInterface
     */
    private function getEventDispatcherMock()
    {
        return $this->getMockBuilder(\Psr\EventDispatcher\EventDispatcherInterface::class)->getMock();
    }
}
