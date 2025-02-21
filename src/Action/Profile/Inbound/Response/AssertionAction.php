<?php

namespace LightSaml\Action\Profile\Inbound\Response;

use LightSaml\Action\ActionInterface;
use LightSaml\Action\DebugPrintTreeActionInterface;
use LightSaml\Action\Profile\AbstractProfileAction;
use LightSaml\Context\Profile\AssertionContext;
use LightSaml\Context\Profile\Helper\MessageContextHelper;
use LightSaml\Context\Profile\ProfileContext;
use Psr\Log\LoggerInterface;

class AssertionAction extends AbstractProfileAction implements DebugPrintTreeActionInterface
{
    public function __construct(LoggerInterface $logger, private readonly ActionInterface $assertionAction)
    {
        parent::__construct($logger);
    }

    protected function doExecute(ProfileContext $context)
    {
        $response = MessageContextHelper::asResponse($context->getInboundContext());

        foreach ($response->getAllAssertions() as $index => $assertion) {
            $name = sprintf('assertion_%s', $index);
            /** @var AssertionContext $assertionContext */
            $assertionContext = $context->getSubContext($name, AssertionContext::class);
            $assertionContext
                ->setAssertion($assertion)
                ->setId($name)
            ;

            $this->assertionAction->execute($assertionContext);
        }
    }

    /**
     * @param int $depth
     *
     * @return array
     */
    public function debugPrintTree($depth = 0)
    {
        $arr = [];
        if ($this->assertionAction instanceof DebugPrintTreeActionInterface) {
            $arr = array_merge($arr, $this->assertionAction->debugPrintTree());
        } else {
            $arr[$this->assertionAction::class] = [];
        }

        return [
            static::class => $arr,
        ];
    }
}
