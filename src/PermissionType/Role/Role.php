<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionType\Role;

use Symfony\Component\Security\Core\User\UserInterface as SecurityUserInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface as SecurityRoleHierarchyInterface;

use Ordermind\LogicalPermissions\PermissionTypeInterface;

/**
 * Permission type for checking a role on a user.
 */
class Role implements PermissionTypeInterface
{
    /**
     * @var Symfony\Component\Security\Core\Role\RoleHierarchyInterface
     */
    protected $roleHierarchy;

    /**
     * @internal
     *
     * @param \Symfony\Component\Security\Core\Role\RoleHierarchyInterface $roleHierarchy RoleHiearchy service
     */
    public function __construct(SecurityRoleHierarchyInterface $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }

    /**
     * {@inheritdoc}
     */
    public static function getName(): string
    {
        return 'role';
    }

    /**
     * Checks if a role is present on a user in a given context
     *
     * @param string $role    The name of the role to evaluate
     * @param array  $context The context for evaluating the role. The context must contain a 'user' key which references either a user string (to signify an anonymous user) or an object implementing Symfony\Component\Security\Core\User\UserInterface. You can get the current user by calling getCurrentUser() from the service 'logauth.service.helper'.
     *
     * @return bool TRUE if the role is present on the user or FALSE if it is not present
     */
    public function checkPermission($role, $context)
    {
        if (!is_string($role)) {
            throw new \TypeError('The role parameter must be a string.');
        }
        if (!$role) {
            throw new \InvalidArgumentException('The role parameter cannot be empty.');
        }
        if (!is_array($context)) {
            throw new \TypeError('The context parameter must be an array.');
        }
        if (!isset($context['user'])) {
            throw new \InvalidArgumentException(sprintf('The context parameter must contain a "user" key to be able to evaluate the %s flag.', $this->getName()));
        }

        $user = $context['user'];
        if (is_string($user)) { //Anonymous user
            return false;
        }

        if (!($user instanceof SecurityUserInterface)) {
            throw new \InvalidArgumentException('The user class must implement Symfony\Component\Security\Core\User\UserInterface to be able to evaluate the user role.');
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
