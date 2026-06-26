<?php

namespace LightSaml\Context;

use IteratorAggregate;

interface ContextInterface extends IteratorAggregate
{
    public function getParent(): ?\LightSaml\Context\ContextInterface;

    public function getTopParent(): \LightSaml\Context\ContextInterface;

    public function setParent(?ContextInterface $parent = null): \LightSaml\Context\ContextInterface;

    
    public function getSubContext(string $name, ?string $class = null): object|null;


    public function getSubContextByClass(string $class, bool $autoCreate): object|null;

    /**
     * @param object|ContextInterface $subContext
     */
    public function addSubContext(string $name, $subContext);

    
    public function removeSubContext(string $name): \LightSaml\Context\ContextInterface;

    
    public function containsSubContext(string $name): bool;

    public function clearSubContexts(): \LightSaml\Context\ContextInterface;

    
    public function debugPrintTree(string $ownName = 'root'): array;

    
    public function getPath(string|array $path): ?\LightSaml\Context\ContextInterface;
}
