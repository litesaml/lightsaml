<?php

namespace LightSaml\Binding;

use LightSaml\Error\LightSamlBindingException;
use Symfony\Component\HttpFoundation\Request;

interface BindingFactoryInterface
{
    /**
     * @return AbstractBinding
     */
    public function getBindingByRequest(Request $request);

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
    public function detectBindingType(Request $request);
}
