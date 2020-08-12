<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Ordermind\LogicalPermissions\BypassAccessCheckerInterface;
use Ordermind\LogicalPermissions\PermissionTypeInterface;

/**
 * Internal proxy service for checking permissions.
 */
interface LogicalPermissionsProxyInterface
{
    /**
     * @internal Add a permission type
     *
     * @param PermissionTypeInterface $type The permission type to add
     */
    public function addType(PermissionTypeInterface $type);

    /**
     * @internal Removes a permission type
     *
     * @param string $name The name of the permission to remove
     */
    public function removeType(string $name);

    /**
     * @internal Checks if a permission type has been registered
     *
     * @param string $name The name of the permission type
     *
     * @return bool TRUE if the permission type has been registered or FALSE if it has not been registered
     */
    public function typeExists(string $name);

    /**
     * @internal Gets all registered permission types
     *
     * @return string[]
     */
    public function getTypes(): array;

    /**
     * @internal Sets the bypass access checker
     *
     * @param BypassAccessCheckerInterface $bypassAccessChecker
     */
    public function setBypassAccessChecker(BypassAccessCheckerInterface $bypassAccessChecker);

    /**
     * @internal Gets the bypass access checker
     *
     * @return BypassAccessCheckerInterface|null the currently registered bypass access checker, or NULL if no bypass
     *                                           access checker has been registered
     */
    public function getBypassAccessChecker(): ?BypassAccessCheckerInterface;

    /**
     * @internal Gets all currently valid permission keys
     *
     * @return string[] Valid permission keys
     */
    public function getValidPermissionKeys(): array;

    /**
     * @internal Checks if access should be granted for a set of permissions in a given context
     *
     * @param array|string|bool $permissions the permission tree to be evaluated
     * @param array             $context     The context for checking access
     * @param bool              $allowBypass (optional) Determines whether bypassing access should possible be allowed.
     *                                       Default value is TRUE.
     *
     * @return bool TRUE if access is granted or FALSE if access is denied
     */
    public function checkAccess($permissions, array $context, bool $allowBypass = true): bool;
}
