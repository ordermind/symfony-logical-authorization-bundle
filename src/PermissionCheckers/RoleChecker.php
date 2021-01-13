<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionCheckers;

use InvalidArgumentException;
use Ordermind\LogicalAuthorizationBundle\PermissionCheckers\Contexts\ContextHasUserInterface;
use Ordermind\LogicalPermissions\PermissionCheckerInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface as SecurityRoleHierarchyInterface;
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
     * @return bool TRUE if the role is present on the user or FALSE if it is not present
     */
    public function checkPermission(string $role, $context): bool
    {
        if (!$role) {
            throw new InvalidArgumentException('The role parameter cannot be empty.');
        }
        if (!($context instanceof ContextHasUserInterface)) {
            throw new TypeError('The context parameter must implement ContextHasUserInterface');
        }

        $user = $context->getUser();
        if (is_string($user)) { // Anonymous user
            return false;
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
