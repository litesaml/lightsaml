<?php

namespace LightSaml\Context;

use IteratorAggregate;

interface ContextInterface extends IteratorAggregate
{
    public function getParent(): ?ContextInterface;

    public function getTopParent(): ContextInterface;

    public function setParent(?ContextInterface $parent = null): ContextInterface;

    public function getSubContext(string $name, ?string $class = null): object|null;

    public function getSubContextByClass(string $class, bool $autoCreate): object|null;

    /**
     * @param object|ContextInterface $subContext
     */
    public function addSubContext(string $name, $subContext);

    public function removeSubContext(string $name): ContextInterface;

    public function containsSubContext(string $name): bool;

    public function clearSubContexts(): ContextInterface;

    public function debugPrintTree(string $ownName = 'root'): array;

    public function getPath(string|array $path): ?ContextInterface;
}
