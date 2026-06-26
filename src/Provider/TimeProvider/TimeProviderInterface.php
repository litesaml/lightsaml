<?php

namespace LightSaml\Provider\TimeProvider;

use DateTime;

interface TimeProviderInterface
{
    public function getTimestamp(): int;

    public function getDateTime(): \DateTime;
}
