<?php

namespace LightSaml\Context;

use ArrayIterator;
use Stringable;

abstract class AbstractContext implements ContextInterface, Stringable
{
    private ?ContextInterface $parent = null;

    /** @var array<string, ContextInterface> */
    private array $subContexts = [];

    public function getParent(): ?ContextInterface
    {
        return $this->parent;
    }

    public function getTopParent(): ContextInterface
    {
        if ($this->getParent() instanceof ContextInterface) {
            return $this->getParent()->getTopParent();
        }

        return $this;
    }

    public function setParent(?ContextInterface $parent = null): ContextInterface
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @template T of ContextInterface
     *
     * @param class-string<T>|null $class
     *
     * @return ($class is null ? ?ContextInterface : T)
     */
    public function getSubContext(string $name, ?string $class = null): ?ContextInterface
    {
        if (isset($this->subContexts[$name])) {
            return $this->subContexts[$name];
        }

        if ($class) {
            $result = $this->createSubContext($class);
            $this->addSubContext($name, $result);

            return $result;
        }

        return null;
    }

    /**
     * @template T of ContextInterface
     *
     * @param class-string<T> $class
     *
     * @return ($autoCreate is true ? T : ?T)
     */
    public function getSubContextByClass(string $class, bool $autoCreate): ?ContextInterface
    {
        return $this->getSubContext($class, $autoCreate ? $class : null);
    }

    public function addSubContext(string $name, ContextInterface $subContext): static
    {
        $existing = $this->subContexts[$name] ?? null;
        if ($existing === $subContext) {
            return $this;
        }

        $this->subContexts[$name] = $subContext;
        $subContext->setParent($this);

        if ($existing instanceof ContextInterface) {
            $existing->setParent(null);
        }

        return $this;
    }

    public function removeSubContext(string $name): ContextInterface
    {
        $subContext = $this->getSubContext($name);

        if ($subContext instanceof ContextInterface) {
            $subContext->setParent(null);
            unset($this->subContexts[$name]);
        }

        return $this;
    }

    public function containsSubContext(string $name): bool
    {
        return isset($this->subContexts[$name]);
    }

    public function clearSubContexts(): ContextInterface
    {
        foreach ($this->subContexts as $subContext) {
            $subContext->setParent(null);
        }
        $this->subContexts = [];

        return $this;
    }

    /** @return ArrayIterator<string, ContextInterface> */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->subContexts);
    }

    /** @return array<string, mixed> */
    public function debugPrintTree(string $ownName = 'root'): array
    {
        $result = [
            $ownName => static::class,
        ];

        if ($this->subContexts) {
            $arr = [];
            foreach ($this->subContexts as $name => $subContext) {
                $arr = array_merge($arr, $subContext->debugPrintTree($name));
            }
            $result[$ownName . '__children'] = $arr;
        }

        return $result;
    }

    public function __toString(): string
    {
        return (string) json_encode($this->debugPrintTree(), JSON_PRETTY_PRINT);
    }

    /** @param string|array<int, string> $path */
    public function getPath(string|array $path): ?ContextInterface
    {
        if (is_string($path)) {
            $path = explode('/', $path);
        }

        $name = array_shift($path);
        $subContext = $this->getSubContext($name);
        if (null === $subContext) {
            return null;
        }

        if ($path === []) {
            return $subContext;
        } else {
            return $subContext->getPath($path);
        }
    }

    protected function createSubContext(string $class): ContextInterface
    {
        return new $class();
    }
}
