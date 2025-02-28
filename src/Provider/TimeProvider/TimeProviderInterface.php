<?php

namespace LightSaml\Provider\TimeProvider;

use DateTime;

interface TimeProviderInterface
{
    /**
     * @return int
     */
    public function getTimestamp();

    /**
     * @return DateTime
     */
    public function getDateTime();
}
