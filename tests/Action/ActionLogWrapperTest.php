<?php

namespace Tests\Action;

use LightSaml\Action\ActionInterface;
use LightSaml\Action\ActionLogWrapper;
use LightSaml\Context\ContextInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\BaseTestCase;

class ActionLogWrapperTest extends BaseTestCase
{
    public function test__builds_loggable_action_with_given_logger(): void
    {
        $context = $this->getContextMock();

        $action = $this->getActionMock();
        $action->expects($this->once())
            ->method('execute')
            ->with($context);

        $loggerMock  = $this->getLoggerMock();
        $loggerMock->expects($this->once())
            ->method('debug')
            ->willReturnCallback(function ($pMessage, array $pContext) use ($action, $context): void {
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
     * @return ActionInterface&MockObject
     */
    private function getActionMock(): MockObject
    {
        return $this->getMockBuilder(ActionInterface::class)->getMock();
    }

    /**
     * @return ContextInterface&MockObject
     */
    private function getContextMock(): MockObject
    {
        return $this->getMockBuilder(ContextInterface::class)->getMock();
    }
}
