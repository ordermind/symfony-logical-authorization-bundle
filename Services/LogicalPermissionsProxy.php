<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Ordermind\LogicalPermissions\LogicalPermissions;
use Ordermind\LogicalAuthorizationBundle\PermissionTypes\PermissionTypeInterface;
use Ordermind\LogicalAuthorizationBundle\Services\LogicalPermissionsProxyInterface;

class LogicalPermissionsProxy implements LogicalPermissionsProxyInterface {
  protected $lp;

  public function __construct() {
    $this->lp = new LogicalPermissions();
  }

  public function addType(PermissionTypeInterface $type) {
    try {
      $this->lp->addType($type->getName(), [$type, 'checkPermission']);
    }
    catch(PermissionTypeAlreadyExistsException $e) {
      $class = get_class($e);
      $message = $e->getMessage();
      $exploded = explode('If you want', $message);
      $new_message = $exploded[0] . 'If you want to change the class that handles a permission type, you may do so by overriding the service definition for that permission type.';
      throw new $class($new_message);
    }
  }

  public function removeType($name) {
    $this->lp->removeType($name);
  }

  public function typeExists($name) {
    return $this->lp->typeExists($name);
  }

  public function setBypassCallback($callback) {
    $this->lp->setBypassCallback($callback);
  }

  public function getBypassCallback() {
    return $this->lp->getBypassCallback();
  }

  public function getValidPermissionKeys() {
    return $this->lp->getValidPermissionKeys();
  }

  public function checkAccess($permissions, $context, $allow_bypass = TRUE) {
    return $this->lp->checkAccess($permissions, $context, $allow_bypass);
  }
}
