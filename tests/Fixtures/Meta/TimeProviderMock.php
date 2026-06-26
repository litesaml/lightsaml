<?php

namespace Tests\Fixtures\Meta;

use DateTime;
use LightSaml\Provider\TimeProvider\TimeProviderInterface;

class TimeProviderMock implements TimeProviderInterface
{
    /**
     */
    public function __construct(protected DateTime $value = new DateTime())
    {
    }

    public function setNow(DateTime $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getTimestamp(): int
    {
        return $this->value->getTimestamp();
    }

    public function getDateTime(): \DateTime
    {
        return $this->value;
    }
}
