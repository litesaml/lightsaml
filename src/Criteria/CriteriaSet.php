<?php

namespace LightSaml\Criteria;

class CriteriaSet
{
    /** @var array|CriteriaInterface[] */
    protected $criterions = [];

    /**
     * @param CriteriaInterface[] $criterions
     */
    public function __construct(array $criterions = [])
    {
        foreach ($criterions as $criterion) {
            $this->add($criterion);
        }
    }

    public function add(CriteriaInterface $criteria): static
    {
        $this->criterions[] = $criteria;

        return $this;
    }

    public function addIfNone(CriteriaInterface $criteria): static
    {
        if (false == $this->has($criteria::class)) {
            $this->add($criteria);
        }

        return $this;
    }

    public function addAll(CriteriaSet $criteriaSet): static
    {
        foreach ($criteriaSet->all() as $criteria) {
            $this->add($criteria);
        }

        return $this;
    }

    public function addIf(mixed $condition, callable $callback): static
    {
        if ($condition) {
            $criteria = call_user_func($callback);
            if ($criteria) {
                $this->add($criteria);
            }
        }

        return $this;
    }

    /**
     * @return CriteriaInterface[]|array
     */
    public function all(): array
    {
        return $this->criterions;
    }

    /**
     * @return array|CriteriaInterface[]
     */
    public function get(string $class): array
    {
        $result = [];
        foreach ($this->criterions as $criteria) {
            if ($criteria instanceof $class) {
                $result[] = $criteria;
            }
        }

        return $result;
    }

    /**
     * @return CriteriaInterface|null
     */
    public function getSingle(string $class): ?object
    {
        foreach ($this->criterions as $criteria) {
            if ($criteria instanceof $class) {
                return $criteria;
            }
        }

        return null;
    }

    public function has(string $class): bool
    {
        foreach ($this->criterions as $criteria) {
            if ($criteria instanceof $class) {
                return true;
            }
        }

        return false;
    }
}
