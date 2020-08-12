<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Test\Fixtures\PermissionTypes;

use Ordermind\LogicalPermissions\PermissionTypeInterface;

class TestType implements PermissionTypeInterface
{
    public static function getName()
    {
        return 'test';
    }

    public function checkPermission($value, $context)
    {
        return $value === 'yes' ? true : false;
    }
}
