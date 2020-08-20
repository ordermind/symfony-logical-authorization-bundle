<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Routing;

use Ordermind\LogicalPermissions\PermissionTree\RawPermissionTree;

/**
 * Custom route interface that allows for having permissions in a route.
 */
interface RouteInterface
{
    /**
     * Sets permissions for this route.
     *
     * @param RawPermissionTree $rawPermissionTree
     */
    public function setRawPermissionTree(RawPermissionTree $rawPermissionTree);

    /**
     * Gets the permissions for this route.
     *
     * @return RawPermissionTree|null
     */
    public function getRawPermissionTree(): ?RawPermissionTree;
}
