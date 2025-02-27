<?php

namespace LightSaml\Bridge\Pimple\Container;

use Pimple\Container;

/**
 * @deprecated 5.0.0 No longer used by internal code and not recommended
 */
abstract class AbstractPimpleContainer
{
    public function __construct(protected Container $pimple)
    {
    }

    /**
     * @return Container
     */
    public function getPimple()
    {
        return $this->pimple;
    }
}
