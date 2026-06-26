<?php

namespace LightSaml\Binding;

use LightSaml\Error\LightSamlBindingException;
use Psr\Http\Message\ServerRequestInterface;

interface BindingFactoryInterface
{
    /**
     * @return AbstractBinding
     */
    public function getBindingByRequest(ServerRequestInterface $request);

    /**
     * @param string $bindingType
     *
     * @throws LightSamlBindingException
     *
     * @return AbstractBinding
     */
    public function create($bindingType);

    /**
     * @return string|null
     */
    public function detectBindingType(ServerRequestInterface $request);
}
