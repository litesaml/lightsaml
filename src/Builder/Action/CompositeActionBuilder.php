<?php

namespace LightSaml\Builder\Action;

use LightSaml\Action\ActionInterface;
use LightSaml\Action\CompositeAction;

class CompositeActionBuilder implements ActionBuilderInterface
{
    /**
     * int priority => ActionInterface[].
     */
    private array $actions = [];

    protected int $increaseStep = 5;

    private int $biggestPriority = 0;

    public function add(ActionInterface $action, ?int $priority = null): static
    {
        if (null === $priority) {
            ++$this->biggestPriority;
            $priority = $this->biggestPriority;
        } elseif ($priority > $this->biggestPriority) {
            $this->biggestPriority = $priority;
        }

        if (false === isset($this->actions[$priority])) {
            $this->actions[$priority] = [];
        }
        $this->actions[$priority][] = $action;

        return $this;
    }

    public function build(): CompositeAction
    {
        $actions = $this->actions;
        ksort($actions);

        $result = new CompositeAction();
        foreach ($actions as $arr) {
            foreach ($arr as $action) {
                $result->add($action);
            }
        }

        return $result;
    }
}
