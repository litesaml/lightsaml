<?php

namespace LightSaml\Provider\TimeProvider;

use DateTime;

class SystemTimeProvider implements TimeProviderInterface
{
    public function getTimestamp(): int
    {
        return time();
    }

    /**
     * @return DateTime
     */
    public function getDateTime()
    {
        return new DateTime();
    }
}
