<?php

namespace LightSaml\Action\Profile\Outbound\Message;

use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Model\Metadata\AssertionConsumerService;

class ResolveEndpointSpAcsAction extends ResolveEndpointBaseAction
{
    protected function getServiceType(ProfileContext $context): string
    {
        return AssertionConsumerService::class;
    }
}
