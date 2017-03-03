<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

interface LogicalAuthorizationInterface {
  /**
   * Checks if access can be bypassed in the current context. If something goes wrong an error will be logged and the method will return FALSE.
   * @param array $context The context must contain a 'user' key which points to a user string or object. The user can either be a string to signify an anonymous user, or a user object. You can get the current user by calling getCurrentUser() on an instance of the service 'ordermind_logical_authorization.service.user_helper'.
   * @return bool TRUE if access can be bypased or FALSE if access can't be bypassed.
   */
  public function checkBypassAccess($context);
  public function checkAccess($permissions, $context, $allow_bypass = true);
  public function handleError($message, $context);
}
