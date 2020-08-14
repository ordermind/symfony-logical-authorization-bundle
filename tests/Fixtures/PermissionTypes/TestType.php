<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Test\Fixtures\PermissionTypes;

use Ordermind\LogicalPermissions\PermissionCheckerInterface;

class TestType implements PermissionCheckerInterface
{
    public static function getName(): string
    {
        return 'test';
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function checkPermission($value, $context): bool
    {
        return $value === 'yes' ? true : false;
    }
}
