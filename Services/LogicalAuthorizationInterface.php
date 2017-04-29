<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

interface LogicalAuthorizationInterface {

  /**
   * Checks if access should be granted for a set of permissions in the current context. If something goes wrong an error will be logged and the method will return FALSE.
   *
   * @param array|string|bool $permissions The permission tree to be evaluated.
   * @param array $context The context for checking access. By default the context must contain a 'user' key which references either a user string (to signify an anonymous user) or an object implementing Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface. You can get the current user by calling getCurrentUser() from the service 'ordermind_logical_authorization.service.helper'.
   * @param bool $allow_bypass (optional) Determines whether bypassing access should possible be allowed. Default value is TRUE.
   *
   * @return bool TRUE if access is granted or FALSE if access is denied.
   */
  public function checkAccess($permissions, $context, $allow_bypass = true);
}
