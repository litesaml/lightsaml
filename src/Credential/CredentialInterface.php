<?php

namespace LightSaml\Credential;

use LightSaml\Credential\Context\CredentialContextSet;
use RobRichards\XMLSecLibs\XMLSecurityKey;

interface CredentialInterface
{
    public function getEntityId(): string;

    /**
     * One of UsageType constants.
     */
    public function getUsageType(): ?string;

    /**
     * @return string[]
     */
    public function getKeyNames(): array;

    public function getPublicKey(): ?XMLSecurityKey;

    public function getPrivateKey(): ?XMLSecurityKey;

    public function getSecretKey(): ?string;

    public function getCredentialContext(): CredentialContextSet;
}
