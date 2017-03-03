<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

interface LogicalPermissionsManagerInterface {
  public function setBypassCallback($callback);
  public function getValidPermissionKeys();
  public function checkAccess($permissions, $context, $allow_bypass = TRUE);
}
