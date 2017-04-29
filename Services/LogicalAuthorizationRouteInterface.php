<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

interface LogicalAuthorizationRouteInterface {

  public function getAvailableRoutes($user = null);

  /**
   * Checks route access for a given user. If something goes wrong an error will be logged and the method will return FALSE.
   * @param string $routeName The name of the route
   * @param string|object $user (optional)  Either a user object or a string to signify an anonymous user. If no user is supplied, the current user will be used.
   *
   * @return bool TRUE if access is granted or FALSE if access is denied. If no user is supplied
   */
  public function checkRouteAccess($routeName, $user = null);
}
