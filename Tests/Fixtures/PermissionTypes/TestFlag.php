<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Tests\Fixtures\PermissionTypes;

use Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag\FlagInterface;

class TestFlag implements FlagInterface {
  protected $name;

  public function getName(): string {
    return $this->name;
  }

  public function setName($name) {
    $this->name = $name;
  }

  public function checkFlag(array $context): bool {
    return true;
  }
}

