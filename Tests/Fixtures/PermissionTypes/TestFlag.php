<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\Fixtures\PermissionTypes;

use Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag\Flags\FlagInterface;

class TestFlag implements FlagInterface {
  protected $name;

  public function getName() {
    return $this->name;
  }

  public function setName($name) {
    $this->name = $name;
  }

  public function checkFlag($context) {
    return true;
  }
}

