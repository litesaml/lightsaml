<?php

namespace LightSaml\Provider\Session;

interface SessionInfoProviderInterface
{
    public function getAuthnInstant(): int;

    public function getSessionIndex(): string;

    public function getAuthnContextClassRef(): string;
}
