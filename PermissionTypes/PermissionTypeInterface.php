<?php

namespace Ordermind\LogicalAuthorizationBundle\PermissionTypes;

interface PermissionTypeInterface {

  /**
   * Gets the name of the permission type
   *
   * @return string name The permission type's name
   */
  public function getName();

  /**
   * Checks if access should be granted for this permission in the current contenxt
   *
   * @param string $permission The name of the permission to check, for example a certain role or flag
   * @param array $context The context for evaluating the permission
   *
   * @return bool TRUE if access should be granted or FALSE if access should not be granted
   */
  public function checkPermission($permission, $context);
}
