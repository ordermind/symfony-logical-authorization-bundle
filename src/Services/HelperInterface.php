<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Services;

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
}
