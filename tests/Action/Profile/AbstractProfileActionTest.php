<?php

namespace Tests\Action\Profile;

use LightSaml\Action\Profile\AbstractProfileAction;
use LightSaml\Context\ContextInterface;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Error\LightSamlContextException;
use LightSaml\Profile\Profiles;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Tests\BaseTestCase;

class AbstractProfileActionTest extends BaseTestCase
{
    public function test_calls_do_execute_with_profile_context()
    {
        /** @var LoggerInterface $loggerMock */
        $loggerMock = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $action = $this->getAbstractProfileActionMock($loggerMock);
        $profileContext = new ProfileContext(Profiles::METADATA, ProfileContext::ROLE_IDP);
        $action->expects($this->once())
            ->method('doExecute')
            ->with($profileContext);

        $action->execute($profileContext);
    }

    public function test_throws_exception_on_non_profile_context()
    {
        $this->expectExceptionMessage("Expected ProfileContext but got");
        $this->expectException(LightSamlContextException::class);
        /** @var LoggerInterface|MockObject $loggerMock */
        $loggerMock = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $loggerMock->expects($this->once())
            ->method('emergency');

        $action = $this->getAbstractProfileActionMock($loggerMock);
        $context = $this->getContextMock();
        $action->expects($this->never())->method('doExecute');

        $action->execute($context);
    }

    /**
     * @return MockObject|AbstractProfileAction
     */
    private function getAbstractProfileActionMock($loggerMock)
    {
        return $this->getMockForAbstractClass(AbstractProfileAction::class, [$loggerMock]);
    }

    /**
     * @return MockObject|ContextInterface
     */
    private function getContextMock()
    {
        return $this->getMockBuilder(ContextInterface::class)->getMock();
    }
}
