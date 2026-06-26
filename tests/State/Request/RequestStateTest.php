<?php

namespace Tests\State\Request;

use LightSaml\Meta\ParameterBag;
use LightSaml\State\Request\RequestState;
use Tests\BaseTestCase;

class RequestStateTest extends BaseTestCase
{
    public function test_can_be_constructed_without_arguments(): void
    {
        new RequestState();
        $this->assertTrue(true);
    }

    public function test_can_be_constructed_wit_id_argument(): void
    {
        new RequestState('id');
        $this->assertTrue(true);
    }

    #[\PHPUnit\Framework\Attributes\Group('deprecated')]
    public function test_can_be_constructed_wit_id_and_nonce_argument(): void
    {
        new RequestState('id', 'nonce');
        $this->assertTrue(true);
    }

    public function test_returns_id(): void
    {
        $state = new RequestState($expectedId = 'id');
        $this->assertEquals($expectedId, $state->getId());
    }

    public function test_has_parameters(): void
    {
        $state = new RequestState();
        $this->assertInstanceOf(ParameterBag::class, $state->getParameters());
    }
}
