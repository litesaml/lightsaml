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

    /**
     * @return CriteriaSet
     */
    public function add(CriteriaInterface $criteria)
    {
        $this->criterions[] = $criteria;

        return $this;
    }

    /**
     * @return CriteriaSet
     */
    public function addIfNone(CriteriaInterface $criteria)
    {
        if (false == $this->has($criteria::class)) {
            $this->add($criteria);
        }

        return $this;
    }

    /**
     * @return CriteriaSet
     */
    public function addAll(CriteriaSet $criteriaSet)
    {
        foreach ($criteriaSet->all() as $criteria) {
            $this->add($criteria);
        }

        return $this;
    }

    /**
     * @param callable $callback
     *
     * @return CriteriaSet
     */
    public function addIf(mixed $condition, $callback)
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
    public function all()
    {
        return $this->criterions;
    }

    /**
     * @param string $class
     *
     * @return array|CriteriaInterface[]
     */
    public function get($class)
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
     * @param string $class
     *
     * @return CriteriaInterface|null
     */
    public function getSingle($class)
    {
        foreach ($this->criterions as $criteria) {
            if ($criteria instanceof $class) {
                return $criteria;
            }
        }

        return;
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    public function has($class)
    {
        foreach ($this->criterions as $criteria) {
            if ($criteria instanceof $class) {
                return true;
            }
        }

        return false;
    }
}
