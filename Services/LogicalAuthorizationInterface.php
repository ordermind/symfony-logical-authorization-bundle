<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

interface LogicalAuthorizationInterface {

  /**
   * Checks if access should be granted for a set of permissions in the current context. If something goes wrong an error will be logged and the method will return FALSE.
   *
   * @param array|string|bool $permissions The permission tree to be evaluated.
   * @param array $context The context for checking access. By default the context must contain a 'user' key which references either a user string (to signify an anonymous user) or an object implementing Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface. You can get the current user by calling getCurrentUser() from the service 'ordermind_logical_authorization.service.user_helper'.
   * @param bool $allow_bypass (optional) Determines whether bypassing access should possible be allowed. Default value is TRUE.
   *
   * @return bool TRUE if access should be granted or FALSE if access should be denied.
   */
  public function checkAccess($permissions, $context, $allow_bypass = true);

  /**
   * @internal Extracts a model from a model manager if applicable. The purpose is to reduce memory footprint in case of an exception.
   *
   * @param mixed $modelManager The model manager to extract the model from. If the parameter is not a model manager it will simply be returned as is.
   *
   * @return mixed If a model manager is provided, the model for the manager is returned. Otherwise the input parameter will be returned.
   */
  public function getRidOfManager($modelManager);
}
