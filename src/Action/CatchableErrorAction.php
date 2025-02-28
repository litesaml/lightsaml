<?php

namespace LightSaml\Action;

use Exception;
use LightSaml\Context\ContextInterface;
use LightSaml\Context\Profile\ExceptionContext;
use LightSaml\Context\Profile\ProfileContexts;

class CatchableErrorAction implements ActionInterface
{
    public function __construct(protected ActionInterface $mainAction, protected ActionInterface $errorAction)
    {
    }

    /**
     * @return void
     */
    public function execute(ContextInterface $context)
    {
        try {
            $this->mainAction->execute($context);
        } catch (Exception $ex) {
            /** @var ExceptionContext $exceptionContext */
            $exceptionContext = $context->getSubContext(ProfileContexts::EXCEPTION, ExceptionContext::class);
            $exceptionContext->addException($ex);

            $this->errorAction->execute($context);
        }
    }
}
