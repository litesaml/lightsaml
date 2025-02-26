<?php

namespace Tests\Action;

use LightSaml\Action\ActionInterface;
use LightSaml\Action\ActionLogWrapper;
use LightSaml\Context\ContextInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\BaseTestCase;

class ActionLogWrapperTest extends BaseTestCase
{
    public function test__builds_loggable_action_with_given_logger()
    {
        $context = $this->getContextMock();

        $action = $this->getActionMock();
        $action->expects($this->once())
            ->method('execute')
            ->with($context);

        $loggerMock  = $this->getLoggerMock();
        $loggerMock->expects($this->once())
            ->method('debug')
            ->willReturnCallback(function ($pMessage, $pContext) use ($action, $context) {
                $expectedMessage = sprintf('Executing action "%s"', $action::class);
                $this->assertEquals($expectedMessage, $pMessage);
                $this->assertArrayHasKey('context', $pContext);
                $this->assertArrayHasKey('action', $pContext);
                $this->assertSame($action, $pContext['action']);
                $this->assertSame($context, $pContext['context']);
            });

        $wrapper = new ActionLogWrapper($loggerMock);

        $wrappedAction = $wrapper->wrap($action);

        $wrappedAction->execute($context);
    }

    /**
     * @return MockObject|ActionInterface
     */
    private function getActionMock()
    {
        return $this->getMockBuilder(ActionInterface::class)->getMock();
    }

    /**
     * @return MockObject|ContextInterface
     */
    private function getContextMock()
    {
        return $this->getMockBuilder(ContextInterface::class)->getMock();
    }
}
