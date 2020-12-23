<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Ordermind\LogicalPermissions\PermissionTree\RawPermissionTree;

/**
 * Internal helper service for logauth.
 */
interface HelperInterface
{
    /**
     * Gets the current user.
     *
     * @return mixed If the current user is authenticated the user object is returned. If the current user is anonymous
     *               the string "anon." is returned. If no current user can be found, NULL is returned.
     */
    public function getCurrentUser();

    /**
     * @internal
     *
     * Logs an error if a logging service is available. Otherwise it outputs the error as a
     * Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException.
     *
     * @param string $message
     * @param array  $context
     */
    public function handleError(string $message, array $context);

    /**
     * @internal
     *
     * Logs a permission check for the debug collector, so that it can be viewed in the debug pages
     *
     * @param bool               $access
     * @param string             $type
     * @param array|string       $item
     * @param object|string|null $user
     * @param RawPermissionTree  $rawPermissionTree
     * @param array              $context
     * @param string             $message
     */
    public function logPermissionCheckForDebug(
        bool $access,
        string $type,
        $item,
        $user,
        RawPermissionTree $rawPermissionTree,
        array $context,
        string $message = ''
    );
}
