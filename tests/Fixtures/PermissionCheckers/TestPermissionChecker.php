<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Test\Fixtures\PermissionCheckers;

use Ordermind\LogicalPermissions\PermissionCheckerInterface;

class TestPermissionChecker implements PermissionCheckerInterface
{
    public function getName(): string
    {
        return 'test';
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function checkPermission(string $value, $context): bool
    {
        return $value === 'yes' ? true : false;
    }
}
