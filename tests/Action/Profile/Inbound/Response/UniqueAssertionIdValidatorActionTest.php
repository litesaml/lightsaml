<?php

// tests/Action/Profile/Inbound/Response/UniqueAssertionIdValidatorActionTest.php

namespace Tests\Action\Profile\Inbound\Response;

use LightSaml\Action\Profile\Inbound\Response\UniqueAssertionIdValidatorAction;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Error\LightSamlContextException;
use LightSaml\Model\Assertion\Assertion;
use LightSaml\Model\Protocol\Response;
use LightSaml\Profile\Profiles;
use Tests\BaseTestCase;

class UniqueAssertionIdValidatorActionTest extends BaseTestCase
{
    private function context(Response $response): ProfileContext
    {
        $context = new ProfileContext(Profiles::SSO_SP_RECEIVE_RESPONSE, ProfileContext::ROLE_SP);
        $context->getInboundContext()->setMessage($response);

        return $context;
    }

    public function test_constructs_with_logger(): void
    {
        new UniqueAssertionIdValidatorAction($this->getLoggerMock());
        $this->assertTrue(true);
    }

    public function test_throws_when_two_assertions_share_an_id(): void
    {
        $this->expectException(LightSamlContextException::class);
        $this->expectExceptionMessage('Response contains more than one assertion with ID "ASSERT_1"');

        $response = new Response();
        $response->addAssertion((new Assertion())->setId('ASSERT_1'));
        $response->addAssertion((new Assertion())->setId('ASSERT_1'));

        (new UniqueAssertionIdValidatorAction($this->getLoggerMock()))->execute($this->context($response));
    }

    public function test_accepts_multiple_assertions_with_distinct_ids(): void
    {
        $response = new Response();
        $response->addAssertion((new Assertion())->setId('ASSERT_1'));
        $response->addAssertion((new Assertion())->setId('ASSERT_2'));

        (new UniqueAssertionIdValidatorAction($this->getLoggerMock()))->execute($this->context($response));

        $this->assertTrue(true);
    }

    public function test_accepts_single_assertion(): void
    {
        $response = new Response();
        $response->addAssertion((new Assertion())->setId('ASSERT_1'));

        (new UniqueAssertionIdValidatorAction($this->getLoggerMock()))->execute($this->context($response));

        $this->assertTrue(true);
    }
}
