<?php

namespace Tests\Action\Profile\Inbound\Response;

use LightSaml\Action\Profile\Inbound\Response\AssertionIdRequiredValidatorAction;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Error\LightSamlContextException;
use LightSaml\Model\Assertion\Assertion;
use LightSaml\Model\Protocol\Response;
use LightSaml\Profile\Profiles;
use Tests\BaseTestCase;

class AssertionIdRequiredValidatorActionTest extends BaseTestCase
{
    private function context(Response $response): ProfileContext
    {
        $context = new ProfileContext(Profiles::SSO_SP_RECEIVE_RESPONSE, ProfileContext::ROLE_SP);
        $context->getInboundContext()->setMessage($response);

        return $context;
    }

    public function test_constructs_with_logger(): void
    {
        new AssertionIdRequiredValidatorAction($this->getLoggerMock());
        $this->assertTrue(true);
    }

    public function test_throws_when_assertion_id_is_null(): void
    {
        $this->expectException(LightSamlContextException::class);
        $this->expectExceptionMessage('Response contains an assertion with a missing or empty ID');

        $response = new Response();
        $response->addAssertion(new Assertion());

        (new AssertionIdRequiredValidatorAction($this->getLoggerMock()))->execute($this->context($response));
    }

    public function test_throws_when_assertion_id_is_empty_string(): void
    {
        $this->expectException(LightSamlContextException::class);
        $this->expectExceptionMessage('Response contains an assertion with a missing or empty ID');

        $response = new Response();
        $response->addAssertion((new Assertion())->setId(''));

        (new AssertionIdRequiredValidatorAction($this->getLoggerMock()))->execute($this->context($response));
    }

    public function test_throws_when_second_assertion_id_is_missing(): void
    {
        $this->expectException(LightSamlContextException::class);
        $this->expectExceptionMessage('Response contains an assertion with a missing or empty ID');

        $response = new Response();
        $response->addAssertion((new Assertion())->setId('ASSERT_1'));
        $response->addAssertion(new Assertion());

        (new AssertionIdRequiredValidatorAction($this->getLoggerMock()))->execute($this->context($response));
    }

    public function test_accepts_assertions_with_ids(): void
    {
        $response = new Response();
        $response->addAssertion((new Assertion())->setId('ASSERT_1'));
        $response->addAssertion((new Assertion())->setId('ASSERT_2'));

        (new AssertionIdRequiredValidatorAction($this->getLoggerMock()))->execute($this->context($response));

        $this->assertTrue(true);
    }
}
