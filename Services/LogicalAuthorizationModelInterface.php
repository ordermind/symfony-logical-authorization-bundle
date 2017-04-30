<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

interface LogicalAuthorizationModelInterface {

  /**
   * Checks access for an action on a model for a given user. If something goes wrong an error will be logged and the method will return FALSE.
   * @param object $model The model might need to implement certain interfaces depending on the permission types you use.
   * @param string $action Examples of model actions are "create", "read", "update" and "delete".
   * @param object|string $user (optional) Either a user object or a string to signify an anonymous user. If no user is supplied, the current user will be used.
   *
   * @return bool TRUE if access is granted or FALSE if access is denied.
   */
  public function checkModelAccess($model, $action, $user = null);

  /**
   * Checks access for an action on a specific field in a model for a given user. If something goes wrong an error will be logged and the method will return FALSE.
   * @param object $model The model might need to implement certain interfaces depending on the permission types you use.
   * @param string $field_name The name of the field.
   * @param string $action Examples of field actions are "get" and "set".
   * @param object|string $user (optional) Either a user object or a string to signify an anonymous user. If no user is supplied, the current user will be used.
   *
   * @return bool TRUE if access is granted or FALSE if access is denied.
   */
  public function checkFieldAccess($model, $field_name, $action, $user = null);
}
