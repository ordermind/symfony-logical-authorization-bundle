<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\Fixtures\PermissionTypes;

use Ordermind\LogicalAuthorizationBundle\PermissionTypes\PermissionTypeInterface;

class TestType implements PermissionTypeInterface {
  public function getName() {
    return 'test';
  }

  public function checkPermission($value, $context) {
    return $value === 'yes' ? true : false;
  }
}
