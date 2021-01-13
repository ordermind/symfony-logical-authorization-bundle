<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionCheckers\SimpleConditionChecker\Checkers;

use Ordermind\LogicalAuthorizationBundle\PermissionCheckers\Contexts\ContextHasUserInterface;
use Ordermind\LogicalAuthorizationBundle\PermissionCheckers\SimpleConditionChecker\SimpleConditionCheckerInterface;

/**
 * Checks if a user has an account, i.e. not an anonymous user.
 */
class UserHasAccountChecker implements SimpleConditionCheckerInterface
{
    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'user_has_account';
    }

    /**
     * Checks if a user has an account.
     *
     * @return bool TRUE if the user is not a string and FALSE if the user is a string and thereby anonymous
     */
    public function checkCondition(ContextHasUserInterface $context): bool
    {
        $user = $context->getUser();
        if (is_string($user)) { //Anonymous user
            return false;
        }

        return true;
    }
}
