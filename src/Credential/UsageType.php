<?php

namespace LightSaml\Credential;

abstract class UsageType
{
    public const ENCRYPTION = 'encryption';

    public const SIGNING = 'signing';

    public const UNSPECIFIED = null;
}
