<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Routing;

/**
 * Custom route interface that allows for having permissions in a route
 */
interface RouteInterface
{
  /**
   * Sets permissions for this route
   *
   * @param array|string|bool $permissions The permissions for this route
   */
    public function setPermissions($permissions);

  /**
   * Gets the permissions for this route
   *
   * @return array|string|bool The permissions
   */
    public function getPermissions();
}
