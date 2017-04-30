<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

interface LogicalAuthorizationRouteInterface {

  /**
   * Gets a map of all available route paths and route patterns for a given user.
   *
   * This method is primarily meant to facilitate client-side authorization by providing a map of all the possible routes and patterns that the user is allowed to visit. The map has the structure ['routes' => ['route-path-1' => 'route-path-1', ...], route_patterns => ['^route-pattern-1' => '^route-pattern-1', ...]].
   *
   * @param object|string $user (optional) Either a user object or a string to signify an anonymous user. If no user is supplied, the current user will be used.
   *
   * @return array A map of available routes and patterns.
   */
  public function getAvailableRoutes($user = null);

  /**
   * Checks route access for a given user.
   *
   * If something goes wrong an error will be logged and the method will return FALSE. If no permissions are defined for the provided route it will return TRUE.
   *
   * @param string $route_name The name of the route
   * @param object|string $user (optional)  Either a user object or a string to signify an anonymous user. If no user is supplied, the current user will be used.
   *
   * @return bool TRUE if access is granted or FALSE if access is denied.
   */
  public function checkRouteAccess($route_name, $user = null);
}
