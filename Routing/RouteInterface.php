<?php

namespace Ordermind\LogicalAuthorizationBundle\Routing;

interface RouteInterface {
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
