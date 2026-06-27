<?php

namespace LightSaml\Binding;

use LightSaml\Error\LightSamlBindingException;
use Psr\Http\Message\ServerRequestInterface;

interface BindingFactoryInterface
{
    public function getBindingByRequest(ServerRequestInterface $request): AbstractBinding;

    /**
     *
     * @throws LightSamlBindingException
     *
     */
    public function create(string $bindingType): AbstractBinding;

    public function detectBindingType(ServerRequestInterface $request): ?string;
}
