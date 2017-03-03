<?php

namespace Ordermind\LogicalAuthorizationBundle\PermissionTypes;

interface PermissionTypeInterface {
  public function getName();
  public function checkPermission($permission, $context);
}
