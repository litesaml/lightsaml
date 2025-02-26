<?php

namespace Tests\Action;

use Exception;
use LightSaml\Action\ActionInterface;
use LightSaml\Action\CatchableErrorAction;
use LightSaml\Context\AbstractContext;
use LightSaml\Context\ContextInterface;
use LightSaml\Context\Profile\ExceptionContext;
use LightSaml\Context\Profile\ProfileContexts;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\BaseTestCase;

class CatchableErrorActionTest extends BaseTestCase
{
    public function test_constructs_with_two_actions()
    {
        new CatchableErrorAction($this->getActionMock(), $this->getActionMock());
        $this->assertTrue(true);
    }

    public function test_execute_calls_first_action()
    {
        $mainAction =  new CatchableErrorAction(
            $firstAction = $this->getActionMock(),
            $secondAction = $this->getActionMock()
        );
        $context = $this->getContextMock();
        $firstAction->expects($this->once())
            ->method('execute')
            ->with($context);
        $secondAction->expects($this->never())
            ->method('execute');

        $mainAction->execute($context);
    }

    public function test_execute_calls_second_action_if_first_throws_exception_and_add_exception_to_context()
    {
        $mainAction =  new CatchableErrorAction(
            $firstAction = $this->getActionMock(),
            $secondAction = $this->getActionMock()
        );
        $context = $this->getContextMock();
        $firstAction->expects($this->once())
            ->method('execute')
            ->with($context)
            ->willThrowException($exception = new Exception());
        $secondAction->expects($this->once())
            ->method('execute')
            ->with($context)
        ;

        $mainAction->execute($context);

        /** @var ExceptionContext $exceptionContext */
        $exceptionContext = $context->getSubContext(ProfileContexts::EXCEPTION);
        $this->assertNotNull($exceptionContext);
        $this->assertInstanceOf(ExceptionContext::class, $exceptionContext);
        $this->assertSame($exception, $exceptionContext->getException());
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
        return $this->getMockForAbstractClass(AbstractContext::class);
    }
}
