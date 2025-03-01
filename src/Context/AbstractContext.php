<?php

namespace LightSaml\Context;

use ArrayIterator;
use InvalidArgumentException;
use Stringable;

abstract class AbstractContext implements ContextInterface, Stringable
{
    /** @var ContextInterface|null */
    private $parent;

    /** @var ContextInterface[] */
    private $subContexts = [];

    /**
     * @return ContextInterface|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return ContextInterface
     */
    public function getTopParent()
    {
        if ($this->getParent()) {
            return $this->getParent()->getTopParent();
        }

        return $this;
    }

    /**
     * @return ContextInterface
     */
    public function setParent(?ContextInterface $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @param string      $name
     * @param string|null $class
     *
     * @return ContextInterface|null
     */
    public function getSubContext($name, $class = null)
    {
        if (isset($this->subContexts[$name])) {
            return $this->subContexts[$name];
        }

        if ($class) {
            $result = $this->createSubContext($class);
            $this->addSubContext($name, $result);

            return $result;
        }

        return;
    }

    /**
     * @param string $class
     * @param bool   $autoCreate
     *
     * @return ContextInterface|null
     */
    public function getSubContextByClass($class, $autoCreate)
    {
        return $this->getSubContext($class, $autoCreate ? $class : null);
    }

    /**
     * @param string                  $name
     * @param object|ContextInterface $subContext
     *
     * @return AbstractContext
     */
    public function addSubContext($name, $subContext)
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

    /**
     * @param string $name
     *
     * @return ContextInterface
     */
    public function removeSubContext($name)
    {
        $subContext = $this->getSubContext($name, false);

        if ($subContext) {
            $subContext->setParent(null);
            unset($this->subContexts[$name]);
        }

        return $this;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function containsSubContext($name)
    {
        return isset($this->subContexts[$name]);
    }

    /**
     * @return ContextInterface
     */
    public function clearSubContexts()
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

    /**
     * @param string $ownName
     *
     * @return array
     */
    public function debugPrintTree($ownName = 'root')
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

    /**
     * @param string $path
     *
     * @return ContextInterface
     */
    public function getPath($path)
    {
        if (is_string($path)) {
            $path = explode('/', $path);
        } elseif (false === is_array($path)) {
            throw new InvalidArgumentException('Expected string or array');
        }

        $name = array_shift($path);
        $subContext = $this->getSubContext($name);
        if (null == $subContext) {
            return;
        }

        if ($path === []) {
            return $subContext;
        } else {
            return $subContext->getPath($path);
        }
    }

    /**
     * @param string $class
     *
     * @return ContextInterface
     */
    protected function createSubContext($class)
    {
        return new $class();
    }
}
