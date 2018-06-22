<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Tests\Fixtures\PermissionTypes;

use Ordermind\LogicalAuthorizationBundle\PermissionTypes\PermissionTypeInterface;

class TestType implements PermissionTypeInterface {
  public function getName(): string {
    return 'test';
  }

  public function checkPermission(string $value, array $context): bool {
    return $value === 'yes' ? true : false;
  }
}
