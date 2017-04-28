<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

interface LogicalAuthorizationInterface {
  /**
   * Checks if access can be bypassed in the current context. If something goes wrong an error will be logged and the method will return FALSE.
   *
   * @param array $context The context for checking access bypass. By default the context must contain a 'user' key which references either a user string (to signify an anonymous user) or an object implementing Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface. You can get the current user by calling getCurrentUser() from the service 'ordermind_logical_authorization.service.user_helper'.
   *
   * @return bool TRUE if access can be bypassed or FALSE if access can't be bypassed.
   */
  public function checkBypassAccess($context);


  public function checkAccess($permissions, $context, $allow_bypass = true);
  public function handleError($message, $context);
}
