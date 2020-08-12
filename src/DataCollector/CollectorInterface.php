<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\DataCollector;

use Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;
use Symfony\Component\VarDumper\Cloner\Data;

/**
 * Data collector for debugging purposes.
 */
interface CollectorInterface extends LateDataCollectorInterface
{
    /**
     * Gets the full permission tree.
     *
     * @return Data
     */
    public function getPermissionTree(): Data;

    /**
     * Gets the log items that have been collected.
     *
     * @return array
     */
    public function getLog(): array;

    /**
     * Adds a permission check to the log.
     *
     * @param bool              $access      TRUE if access was granted of FALSE if it was denied
     * @param string            $type        the type of item that was the subject of the permission check, for example
     *                                       "route", "model" or "field"
     * @param mixed             $item        The item that was the subject of the permission check, for example a route
     *                                       name
     * @param object|string     $user        The user for which the permissions were checked. Supply either a user
     *                                       object or a string to signify an anonymous user.
     * @param array|string|bool $permissions The permissions that were evaluated
     * @param array             $context     The context of the evaluation
     * @param string            $message     (optional) A message to display in the log
     */
    public function addPermissionCheck(
        bool $access,
        string $type,
        $item,
        $user,
        $permissions,
        array $context,
        string $message = ''
    );
}
