<?php

namespace LightSaml\Resolver\Endpoint\Criteria;

use LightSaml\Criteria\CriteriaInterface;

class BindingCriteria implements CriteriaInterface
{
    /** @var int[] Binding => Preference */
    protected array $bindings = [];

    public function __construct(array $bindings)
    {
        foreach ($bindings as $binding) {
            $this->add($binding);
        }
    }

    public function add(string $binding): static
    {
        $this->bindings[$binding] = count($this->bindings) + 1;

        return $this;
    }

    /**
     * Returns array of bindings ordered by preference, first being most preferable, last least preferable.
     *
     * @return string[]
     */
    public function getAllBindings(): array
    {
        return array_keys($this->bindings);
    }

    /**
     *
     * @return int|null Preference of a binding or null if not preferred
     */
    public function getPreference($binding): ?int
    {
        return $this->bindings[$binding] ?? null;
    }
}
