<?php

namespace LightSaml\Context;

use IteratorAggregate;

interface ContextInterface extends IteratorAggregate
{
    /**
     * @return ContextInterface|null
     */
    public function getParent();

    /**
     * @return ContextInterface
     */
    public function getTopParent();

    /**
     * @return ContextInterface
     */
    public function setParent(?ContextInterface $parent = null);

    /**
     * @param string      $name
     * @param string|null $class
     *
     * @return ContextInterface|null
     */
    public function getSubContext($name, $class = null);

    /**
     * @param string $class
     * @param bool   $autoCreate
     *
     * @return ContextInterface|null
     */
    public function getSubContextByClass($class, $autoCreate);

    /**
     * @param string                  $name
     * @param object|ContextInterface $subContext
     */
    public function addSubContext($name, $subContext);

    /**
     * @param string $name
     *
     * @return ContextInterface
     */
    public function removeSubContext($name);

    /**
     * @param string $name
     *
     * @return bool
     */
    public function containsSubContext($name);

    /**
     * @return ContextInterface
     */
    public function clearSubContexts();

    /**
     * @param string $ownName
     *
     * @return array
     */
    public function debugPrintTree($ownName = 'root');

    /**
     * @param string $path
     *
     * @return ContextInterface
     */
    public function getPath($path);
}
