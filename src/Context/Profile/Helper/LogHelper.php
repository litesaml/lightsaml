<?php

namespace LightSaml\Context\Profile\Helper;

use LightSaml\Action\ActionInterface;
use LightSaml\Context\ContextInterface;
use LightSaml\Context\Profile\ProfileContext;

abstract class LogHelper
{
    /**
     *
     * @return array
     */
    public static function getActionContext(ContextInterface $context, ActionInterface $action, ?array $extraData = null)
    {
        return self::getContext($context, $action, $extraData, false);
    }

    /**
     *
     * @return array
     */
    public static function getActionErrorContext(ContextInterface $context, ActionInterface $action, ?array $extraData = null)
    {
        return self::getContext($context, $action, $extraData, true);
    }

    /**
     * @param bool $logWholeContext
     *
     * @return array
     */
    private static function getContext(ContextInterface $context, ?ActionInterface $action = null, ?array $extraData = null, $logWholeContext = false)
    {
        $topContext = $context->getTopParent();
        $result = [];
        if ($topContext instanceof ProfileContext) {
            $result['profile_id'] = $topContext->getProfileId();
            $result['own_role'] = $topContext->getOwnRole();
        }
        if ($action instanceof ActionInterface) {
            $result['action'] = $action::class;
        }
        $result['top_context_id'] = spl_object_hash($topContext);

        if ($logWholeContext) {
            $result['top_context'] = $topContext;
        }
        if ($extraData) {
            $result = array_merge($result, $extraData);
        }

        return $result;
    }
}
