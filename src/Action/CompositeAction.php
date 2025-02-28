<?php

namespace LightSaml\Action;

use LightSaml\Context\ContextInterface;
use Stringable;

class CompositeAction implements ActionInterface, DebugPrintTreeActionInterface, CompositeActionInterface, Stringable
{
    /** @var ActionInterface[] */
    protected $children = [];

    /**
     * @param ActionInterface[] $children
     */
    public function __construct(array $children = [])
    {
        foreach ($children as $action) {
            $this->add($action);
        }
    }

    /**
     * @return ActionInterface[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return CompositeAction
     */
    public function add(ActionInterface $action)
    {
        $this->children[] = $action;

        return $this;
    }

    /**
     * @param callable $callable
     *
     * @return ActionInterface|null
     */
    public function map($callable)
    {
        foreach ($this->children as $k => $action) {
            $newAction = call_user_func($callable, $action);
            if ($newAction) {
                $this->children[$k] = $newAction;
            }
        }
    }

    /**
     * @return void
     */
    public function execute(ContextInterface $context)
    {
        foreach ($this->children as $action) {
            $action->execute($context);
        }
    }

    /**
     * @return array
     */
    public function debugPrintTree()
    {
        $arr = [];
        foreach ($this->children as $childAction) {
            if ($childAction instanceof DebugPrintTreeActionInterface) {
                $arr = array_merge($arr, $childAction->debugPrintTree());
            } else {
                $arr = array_merge($arr, [$childAction::class => []]);
            }
        }

        return [
            static::class => $arr,
        ];
    }

    public function __toString(): string
    {
        return (string) json_encode($this->debugPrintTree(), JSON_PRETTY_PRINT);
    }
}
