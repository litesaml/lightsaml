<?php

namespace LightSaml\Binding;

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
     * @throws \LightSaml\Error\LightSamlBindingException
     *
     * @return AbstractBinding
     */
    public function create($bindingType);

    /**
     * @return string|null
     */
    public function detectBindingType(Request $request);
}
