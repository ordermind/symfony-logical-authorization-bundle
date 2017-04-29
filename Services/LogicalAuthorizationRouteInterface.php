<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

interface LogicalAuthorizationRouteInterface {

  /**
   * Gets a list of all available routes and route patterns for a given user.
   *
   * @param object|string $user (optional)  Either a user object or a string to signify an anonymous user. If no user is supplied, the current user will be used.
   *
   * @return array An array with the structure ['routes' => ['route_name_1' => route_1, ...], route_patterns => ['^route-pattern-1' => true, ...]]
   */
  public function getAvailableRoutes($user = null);

  /**
   * Checks route access for a given user. If something goes wrong an error will be logged and the method will return FALSE.
   * @param string $route_name The name of the route
   * @param object|string $user (optional)  Either a user object or a string to signify an anonymous user. If no user is supplied, the current user will be used.
   *
   * @return bool TRUE if access is granted or FALSE if access is denied.
   */
  public function checkRouteAccess($route_name, $user = null);
}
