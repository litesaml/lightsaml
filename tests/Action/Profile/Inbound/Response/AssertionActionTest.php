<?php

namespace Tests\Action\Profile\Inbound\Response;

use LightSaml\Action\ActionInterface;
use LightSaml\Action\Profile\Inbound\Response\AssertionAction;
use LightSaml\Context\Profile\AssertionContext;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Model\Assertion\Assertion;
use LightSaml\Model\Protocol\Response;
use LightSaml\Profile\Profiles;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\BaseTestCase;
use Tests\Mock\Action\FooAction;

class AssertionActionTest extends BaseTestCase
{
    public function test_constructs_with_logger_and_action()
    {
        new AssertionAction($this->getLoggerMock(), $this->getActionMock());
        $this->assertTrue(true);
    }

    public function test_calls_action_for_each_assertion()
    {
        $action = new AssertionAction($this->getLoggerMock(), $assertionActionMock = $this->getActionMock());

        $context = new ProfileContext(Profiles::SSO_IDP_RECEIVE_AUTHN_REQUEST, ProfileContext::ROLE_IDP);
        $context->getInboundContext()->setMessage($response = new Response());
        $response
            ->addAssertion($assertion1 = new Assertion())
            ->addAssertion($assertion2 = new Assertion())
        ;

        $assertion1Called = $assertion2Called = false;
        $assertionActionMock->expects($this->exactly(2))
            ->method('execute')
            ->with($this->isInstanceOf(AssertionContext::class))
            ->willReturnCallback(function (AssertionContext $assertionContext) use (&$assertion1, &$assertion2, &$assertion1Called, &$assertion2Called) {
                if ($assertionContext->getAssertion() === $assertion1) {
                    $assertion1Called = true;
                } elseif ($assertionContext->getAssertion() === $assertion2) {
                    $assertion2Called = true;
                } else {
                    $this->fail('Unknown assertion');
                }
            })
        ;

        $action->execute($context);

        $this->assertTrue($assertion1Called);
        $this->assertTrue($assertion2Called);
    }

    public function test_creates_context_for_each_assertion()
    {
        $action = new AssertionAction($this->getLoggerMock(), $assertionActionMock = $this->getActionMock());

        $context = new ProfileContext(Profiles::SSO_IDP_RECEIVE_AUTHN_REQUEST, ProfileContext::ROLE_IDP);
        $context->getInboundContext()->setMessage($response = new Response());
        $response
            ->addAssertion($assertion1 = new Assertion())
            ->addAssertion($assertion2 = new Assertion())
        ;

        $action->execute($context);

        /** @var AssertionContext $assertionContext */
        $assertionContext = $context->getSubContext('assertion_0');
        $this->assertInstanceOf(AssertionContext::class, $assertionContext);
        $this->assertSame($assertion1, $assertionContext->getAssertion());

        $assertionContext = $context->getSubContext('assertion_1');
        $this->assertInstanceOf(AssertionContext::class, $assertionContext);
        $this->assertSame($assertion2, $assertionContext->getAssertion());
    }

    public function test_debug_tree()
    {
        $innerAction = new AssertionAction($this->getLoggerMock(), new FooAction());
        $outerAction = new AssertionAction($this->getLoggerMock(), $innerAction);

        $actualTree = $outerAction->debugPrintTree();

        $expectedTree = [
            AssertionAction::class => [
                AssertionAction::class => [
                    FooAction::class => [],
                ],
            ],
        ];

        $this->assertEquals($expectedTree, $actualTree);
    }

    /**
     * @return MockObject|ActionInterface
     */
    private function getActionMock()
    {
        return $this->getMockBuilder(ActionInterface::class)->getMock();
    }
}
