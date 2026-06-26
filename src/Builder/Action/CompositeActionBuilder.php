<?php

namespace LightSaml\Builder\Action;

use InvalidArgumentException;
use LightSaml\Action\ActionInterface;
use LightSaml\Action\CompositeAction;

class CompositeActionBuilder implements ActionBuilderInterface
{
    /**
     * int priority => ActionInterface[].
     */
    private array $actions = [];

    /** @var int */
    protected $increaseStep = 5;

    private int $biggestPriority = 0;

    public function add(ActionInterface $action, int|bool $priority = false): static
    {
        if (false === $priority) {
            ++$this->biggestPriority;
            $priority = $this->biggestPriority;
        } elseif (false === is_int($priority)) {
            throw new InvalidArgumentException('Expected integer value for priority');
        } elseif ($priority > $this->biggestPriority) {
            $this->biggestPriority = $priority;
        }

        if (false === isset($this->actions[$priority])) {
            $this->actions[$priority] = [];
        }
        $this->actions[$priority][] = $action;

        return $this;
    }

    public function build(): \LightSaml\Action\CompositeAction
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
