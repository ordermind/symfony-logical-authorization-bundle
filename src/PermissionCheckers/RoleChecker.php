<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionCheckers;

use InvalidArgumentException;
use Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface;
use Ordermind\LogicalPermissions\PermissionCheckerInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface as SecurityRoleHierarchyInterface;
use Symfony\Component\Security\Core\User\UserInterface as SecurityUserInterface;
use TypeError;

/**
 * Checks role permissions.
 */
class RoleChecker implements PermissionCheckerInterface
{
    protected SecurityRoleHierarchyInterface $roleHierarchy;

    public function __construct(SecurityRoleHierarchyInterface $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'role';
    }

    /**
     * Checks if a role is present on a user in a given context.
     *
     * @param string $role    The name of the role to evaluate
     * @param array  $context The context for evaluating the role. The context must contain a 'user' key which
     *                        references either a user string (to signify an anonymous user) or an object implementing
     *                        Symfony\Component\Security\Core\User\UserInterface. You can get the current user by
     *                        calling getCurrentUser() from the service 'logauth.service.helper'.
     *
     * @return bool TRUE if the role is present on the user or FALSE if it is not present
     */
    public function checkPermission(string $role, $context): bool
    {
        if (!$role) {
            throw new InvalidArgumentException('The role parameter cannot be empty.');
        }
        if (!is_array($context)) {
            throw new TypeError('The context parameter must be an array.');
        }
        if (!isset($context['user'])) {
            throw new InvalidArgumentException(
                sprintf(
                    'The context parameter must contain a "user" key to be able to evaluate the %s permission.',
                    $this->getName()
                )
            );
        }

        $user = $context['user'];
        if (is_string($user)) { //Anonymous user
            return false;
        }

        if (!($user instanceof SecurityUserInterface)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The user class must implement %s to be able to evaluate the user role.',
                    UserInterface::class
                )
            );
        }

        $roles = $this->roleHierarchy->getReachableRoleNames($user->getRoles());

        foreach ($roles as $thisRole) {
            if ($role === $thisRole) {
                return true;
            }
        }

        return false;
    }
}
