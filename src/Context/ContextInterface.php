<?php

namespace LightSaml\Context;

use IteratorAggregate;

/** @extends IteratorAggregate<string, ContextInterface> */
interface ContextInterface extends IteratorAggregate
{
    public function getParent(): ?ContextInterface;

    public function getTopParent(): ContextInterface;

    public function setParent(?ContextInterface $parent = null): ContextInterface;

    /**
     * @template T of ContextInterface
     * @param class-string<T>|null $class
     * @return ($class is null ? ?ContextInterface : T)
     */
    public function getSubContext(string $name, ?string $class = null): ?ContextInterface;

    /**
     * @template T of ContextInterface
     * @param class-string<T> $class
     * @return ($autoCreate is true ? T : ?T)
     */
    public function getSubContextByClass(string $class, bool $autoCreate): ?ContextInterface;

    public function addSubContext(string $name, ContextInterface $subContext): static;

    public function removeSubContext(string $name): ContextInterface;

    public function containsSubContext(string $name): bool;

    public function clearSubContexts(): ContextInterface;

    /** @return array<string, mixed> */
    public function debugPrintTree(string $ownName = 'root'): array;

    /** @param string|array<int, string> $path */
    public function getPath(string|array $path): ?ContextInterface;
}
