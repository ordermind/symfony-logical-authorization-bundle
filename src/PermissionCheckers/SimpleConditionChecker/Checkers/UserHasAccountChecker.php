<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionCheckers\SimpleConditionChecker\Checkers;

use InvalidArgumentException;
use Ordermind\LogicalAuthorizationBundle\PermissionCheckers\SimpleConditionChecker\SimpleConditionCheckerInterface;

/**
 * Checks if a user has an account, i.e. not an anonymous user.
 */
class UserHasAccountChecker implements SimpleConditionCheckerInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getName(): string
    {
        return 'user_has_account';
    }

    /**
     * Checks if a user has an account in a given context.
     *
     * @param array $context The context for evaluating the condition. The context must contain a 'user' key so that the
     *                       user can be evaluated. You can get the current user by calling getCurrentUser() from the
     *                       service 'logauth.service.helper'.
     *
     * @return bool TRUE if the user is not a string and FALSE if the user is a string and thereby anonymous
     */
    public function checkCondition(array $context): bool
    {
        if (!isset($context['user'])) {
            throw new InvalidArgumentException(
                sprintf(
                    'The context parameter must contain a "user" key to be able to evaluate the %s condition.',
                    $this->getName()
                )
            );
        }

        $user = $context['user'];
        if (is_string($user)) { //Anonymous user
            return false;
        }

        return true;
    }
}
