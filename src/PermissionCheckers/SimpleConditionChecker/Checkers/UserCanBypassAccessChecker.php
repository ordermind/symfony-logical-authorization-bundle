<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionCheckers\SimpleConditionChecker\Checkers;

use Ordermind\LogicalAuthorizationBundle\PermissionCheckers\Contexts\ContextHasUserInterface;
use Ordermind\LogicalAuthorizationBundle\PermissionCheckers\SimpleConditionChecker\SimpleConditionCheckerInterface;

class UserCanBypassAccessChecker implements SimpleConditionCheckerInterface
{
    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'user_can_bypass_access';
    }

    /**
     * Checks if access can be bypassed.
     *
     * @return bool TRUE if access can be bypassed or FALSE if access can't be bypassed
     */
    public function checkCondition(ContextHasUserInterface $context): bool
    {
        $user = $context->getUser();
        if (is_string($user)) { //Anonymous user
            return false;
        }

        return $user->getBypassAccess();
    }
}
