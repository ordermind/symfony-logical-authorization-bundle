<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Ordermind\LogicalAuthorizationBundle\ValueObjects\RawPermissionTree;

/**
 * Internal service for checking access.
 */
interface LogicalAuthorizationInterface
{
    /**
     * Checks if access should be granted for a set of permissions in a given context. If something goes wrong an error
     * will be logged and the method will return FALSE.
     *
     * @param array $context     The context for checking access. By default the context must contain
     *                           a 'user' key which references either a user string (to signify an
     *                           anonymous user) or an object implementing
     *                           Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface. You
     *                           can get the current user by calling getCurrentUser() from the service
     *                           'logauth.service.helper'.
     * @param bool  $allowBypass (optional) Determines whether bypassing access should possible be
     *                           allowed. Default value is TRUE.
     *
     * @return bool TRUE if access is granted or FALSE if access is denied
     */
    public function checkAccess(RawPermissionTree $rawPermissionTree, array $context, bool $allowBypass = true): bool;
}
