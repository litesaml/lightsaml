<?php

namespace LightSaml\Context\Profile;

use LightSaml\Context\AbstractContext;
use LightSaml\Error\LightSamlContextException;

abstract class AbstractProfileContext extends AbstractContext
{
    public function getProfileContext(): ProfileContext
    {
        $result = $this;
        while ($result && false == $result instanceof ProfileContext) {
            $result = $result->getParent();
        }

        if ($result instanceof ProfileContext) {
            return $result;
        }

        throw new LightSamlContextException($this, 'Missing ProfileContext');
    }
}
