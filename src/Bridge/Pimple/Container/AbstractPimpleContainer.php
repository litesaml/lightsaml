<?php

namespace LightSaml\Bridge\Pimple\Container;

use Pimple\Container;

abstract class AbstractPimpleContainer
{
    public function __construct(protected \Pimple\Container $pimple)
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
