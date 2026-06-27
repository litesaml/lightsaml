<?php

namespace LightSaml\Credential\Context;

use InvalidArgumentException;

class CredentialContextSet
{
    /** @var CredentialContextInterface[] */
    protected array $contexts = [];

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
    public function all(): array
    {
        return $this->contexts;
    }

    
    public function get(string $class): ?\LightSaml\Credential\Context\CredentialContextInterface
    {
        foreach ($this->contexts as $context) {
            if ($context::class == $class || is_subclass_of($context, $class)) {
                return $context;
            }
        }

        return null;
    }
}
