<?php

namespace Tests\Action\Profile;

use LightSaml\Action\Profile\FlushRequestStatesAction;
use LightSaml\Context\Profile\AssertionContext;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Context\Profile\ProfileContexts;
use LightSaml\Context\Profile\RequestStateContext;
use LightSaml\Profile\Profiles;
use LightSaml\State\Request\RequestState;
use LightSaml\Store\Request\RequestStateStoreInterface;
use Mockery;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use Psr\Log\LoggerInterface;
use Tests\BaseTestCase;

class FlushRequestStatesActionTest extends BaseTestCase
{
    public function test_constructs_with_logger_and_request_state_store()
    {
        $loggerMock = $this->getLoggerMock();
        $requestStoreMock = $this->getRequestStateStoreMock();

        new FlushRequestStatesAction($loggerMock, $requestStoreMock);

        $this->assertTrue(true);
    }

    #[DoesNotPerformAssertions]
    public function test_flushes_store_with_inbound_request_state()
    {
        $expectedIds = ['1111', '2222', '3333'];

        $requestStoreMock = Mockery::mock(RequestStateStoreInterface::class, function ($mock) use ($expectedIds) {
            $mock->shouldReceive('remove')
                ->once()
                ->with($this->equalTo($expectedIds[0]))
                ->andReturn(true);
            $mock->shouldReceive('remove')
                ->once()
                ->with($this->equalTo($expectedIds[1]))
                ->andReturn(true);
            $mock->shouldReceive('remove')
                ->once()
                ->with($this->equalTo($expectedIds[2]))
                ->andReturn(false);
        });

        $loggerMock = Mockery::mock(LoggerInterface::class, function ($mock) use ($expectedIds) {
            $mock->shouldReceive('debug')
                ->once()
                ->with($this->equalTo(sprintf('Removed request state "%s"', $expectedIds[0])), $this->isType('array'));

            $mock->shouldReceive('debug')
                ->once()
                ->with($this->equalTo(sprintf('Removed request state "%s"', $expectedIds[1])), $this->isType('array'));

            $mock->shouldReceive('warning')
                ->once()
                ->with($this->equalTo(sprintf('Request state "%s" does not exist', $expectedIds[2])), $this->isType('array'));
        });

        $action = new FlushRequestStatesAction($loggerMock, $requestStoreMock);

        $context = new ProfileContext(Profiles::METADATA, ProfileContext::ROLE_IDP);
        $context->getInboundContext()
            ->addSubContext(
                ProfileContexts::REQUEST_STATE,
                (new RequestStateContext())->setRequestState(new RequestState($expectedIds[0]))
            );
        $context->addSubContext(
            'assertion_1',
            (new AssertionContext())
                ->addSubContext(
                    ProfileContexts::REQUEST_STATE,
                    (new RequestStateContext())->setRequestState(new RequestState($expectedIds[1]))
                )
        );
        $context->addSubContext(
            'assertion_2',
            (new AssertionContext())
                ->addSubContext(
                    ProfileContexts::REQUEST_STATE,
                    (new RequestStateContext())->setRequestState(new RequestState($expectedIds[2]))
                )
        );

        $action->execute($context);
    }
}
