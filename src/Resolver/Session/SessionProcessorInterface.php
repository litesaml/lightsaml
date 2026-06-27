<?php

namespace LightSaml\Resolver\Session;

use LightSaml\Model\Assertion\Assertion;

interface SessionProcessorInterface
{
    /**
     * @param Assertion[] $assertions
     */
    public function processAssertions(array $assertions, string $ownEntityId, string $partyEntityId): void;
}
