<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Test\Fixtures\BypassAccessChecker;

use Ordermind\LogicalPermissions\BypassAccessCheckerInterface;

class AlwaysDeny implements BypassAccessCheckerInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function checkBypassAccess($context): bool
    {
        return false;
    }
}
