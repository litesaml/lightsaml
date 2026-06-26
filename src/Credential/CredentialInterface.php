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

    public function getPublicKey(): ?\RobRichards\XMLSecLibs\XMLSecurityKey;

    public function getPrivateKey(): ?\RobRichards\XMLSecLibs\XMLSecurityKey;

    public function getSecretKey(): ?string;

    public function getCredentialContext(): \LightSaml\Credential\Context\CredentialContextSet;
}
