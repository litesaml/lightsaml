<?php

namespace LightSaml\Binding;

use LightSaml\Error\LightSamlBindingException;
use Psr\Http\Message\ServerRequestInterface;

interface BindingFactoryInterface
{
    public function getBindingByRequest(ServerRequestInterface $request): \LightSaml\Binding\AbstractBinding;

    /**
     *
     * @throws LightSamlBindingException
     *
     */
    public function create(string $bindingType): \LightSaml\Binding\AbstractBinding;

    public function detectBindingType(ServerRequestInterface $request): ?string;
}
