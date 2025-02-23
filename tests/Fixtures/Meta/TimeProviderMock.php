<?php

namespace LightSaml\Tests\Fixtures\Meta;

use LightSaml\Provider\TimeProvider\TimeProviderInterface;

class TimeProviderMock implements TimeProviderInterface
{
    /**
     * @param \DateTime $value
     */
    public function __construct(protected ?\DateTime $value = null)
    {
    }

    /**
     * @return TimeProviderMock
     */
    public function setNow(\DateTime $value)
    {
        $this->value = $value;

        return $this;
    }

    public function getTimestamp(): int
    {
        return $this->value->getTimestamp();
    }

    /**
     * @return \DateTime
     */
    public function getDateTime()
    {
        return $this->value;
    }
}
