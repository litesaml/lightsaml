<?php

namespace LightSaml\Context;

use ArrayIterator;
use InvalidArgumentException;
use Stringable;

abstract class AbstractContext implements ContextInterface, Stringable
{
    private ?ContextInterface $parent = null;

    /** @var ContextInterface[] */
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

    public function getSubContext(string $name, ?string $class = null): object|null
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

    public function getSubContextByClass(string $class, bool $autoCreate): object|null
    {
        return $this->getSubContext($class, $autoCreate ? $class : null);
    }

    /**
     * @param object|ContextInterface $subContext
     *
     */
    public function addSubContext(string $name, $subContext): AbstractContext
    {
        if (false === is_object($subContext)) {
            throw new InvalidArgumentException('Expected object or ContextInterface');
        }

        $existing = $this->subContexts[$name] ?? null;
        if ($existing === $subContext) {
            return $this;
        }

        $this->subContexts[$name] = $subContext;
        if ($subContext instanceof ContextInterface) {
            $subContext->setParent($this);
        }

        if ($existing instanceof ContextInterface) {
            $existing->setParent(null);
        }

        return $this;
    }

    public function removeSubContext(string $name): ContextInterface
    {
        $subContext = $this->getSubContext($name, false);

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

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->subContexts);
    }

    public function debugPrintTree(string $ownName = 'root'): array
    {
        $result = [
            $ownName => static::class,
        ];

        if ($this->subContexts) {
            $arr = [];
            foreach ($this->subContexts as $name => $subContext) {
                if ($subContext instanceof ContextInterface) {
                    $arr = array_merge($arr, $subContext->debugPrintTree($name));
                } else {
                    $arr = array_merge($arr, [$name => $subContext::class]);
                }
            }
            $result[$ownName . '__children'] = $arr;
        }

        return $result;
    }

    public function __toString(): string
    {
        return (string) json_encode($this->debugPrintTree(), JSON_PRETTY_PRINT);
    }

    public function getPath(string|array $path): ?ContextInterface
    {
        if (is_string($path)) {
            $path = explode('/', $path);
        } elseif (false === is_array($path)) {
            throw new InvalidArgumentException('Expected string or array');
        }

        $name = array_shift($path);
        $subContext = $this->getSubContext($name);
        if (null == $subContext) {
            return null;
        }

        if ($path === []) {
            return $subContext;
        } else {
            return $subContext->getPath($path);
        }
    }

    protected function createSubContext(string $class): object
    {
        return new $class();
    }
}
