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
    public function getChildren(): array
    {
        return $this->children;
    }

    public function add(ActionInterface $action): static
    {
        $this->children[] = $action;

        return $this;
    }

    public function map(callable $callable): void
    {
        foreach ($this->children as $k => $action) {
            $newAction = call_user_func($callable, $action);
            if ($newAction) {
                $this->children[$k] = $newAction;
            }
        }
    }

    public function execute(ContextInterface $context): void
    {
        foreach ($this->children as $action) {
            $action->execute($context);
        }
    }

    public function debugPrintTree(): array
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
