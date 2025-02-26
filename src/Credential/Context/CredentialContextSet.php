<?php

namespace LightSaml\Credential\Context;

use InvalidArgumentException;

class CredentialContextSet
{
    /** @var CredentialContextInterface[] */
    protected $contexts = [];

    /**
     * @param CredentialContextInterface[] $contexts
     */
    public function __construct(array $contexts = [])
    {
        foreach ($contexts as $context) {
            if (false == $context instanceof CredentialContextInterface) {
                throw new InvalidArgumentException('Expected CredentialContextInterface');
            }
            $this->contexts[] = $context;
        }
    }

    /**
     * @return CredentialContextInterface[]
     */
    public function all()
    {
        return $this->contexts;
    }

    /**
     * @param string $class
     *
     * @return CredentialContextInterface|null
     */
    public function get($class)
    {
        foreach ($this->contexts as $context) {
            if ($context::class == $class || is_subclass_of($context, $class)) {
                return $context;
            }
        }

        return;
    }
}
