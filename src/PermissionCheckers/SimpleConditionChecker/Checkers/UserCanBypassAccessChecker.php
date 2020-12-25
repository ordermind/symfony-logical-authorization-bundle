<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionCheckers\SimpleConditionChecker\Checkers;

use InvalidArgumentException;
use Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface;
use Ordermind\LogicalAuthorizationBundle\PermissionCheckers\SimpleConditionChecker\SimpleConditionCheckerInterface;
use UnexpectedValueException;

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
     * Checks if access can be bypassed in a given context.
     *
     * @param array $context The context for evaluating the condition. The context must contain a 'user' key which
     *                       references either a user string (to signify an anonymous user) or an object implementing
     *                       Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface. You can get the current
     *                       user by calling getCurrentUser() from the service 'logauth.service.helper'.
     *
     * @return bool TRUE if access can be bypassed or FALSE if access can't be bypassed
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
        if (!($user instanceof UserInterface)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The user class must implement Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface to be '
                        . 'able to evaluate the %s condition.',
                    $this->getName()
                )
            );
        }

        $access = $user->getBypassAccess();
        if (!is_bool($access)) {
            throw new UnexpectedValueException(
                sprintf(
                    'The method getBypassAccess() on the user object must return a boolean. Returned type is %s.',
                    gettype($access)
                )
            );
        }

        return $access;
    }
}
