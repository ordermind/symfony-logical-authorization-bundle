<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\BypassAccessChecker;

use Ordermind\LogicalAuthorizationBundle\PermissionType\Flag\Flags\UserCanBypassAccess;
use Ordermind\LogicalPermissions\BypassAccessCheckerInterface;

/**
 * Default bypass access checker.
 */
class BypassAccessChecker implements BypassAccessCheckerInterface
{
    /**
     * @var UserCanBypassAccess
     */
    protected $flagChecker;

    /**
     * @internal
     *
     * @param UserCanBypassAccess $flagChecker
     */
    public function __construct(UserCanBypassAccess $flagChecker)
    {
        $this->flagChecker = $flagChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function checkBypassAccess($context): bool
    {
        return $this->flagChecker->checkFlag($context);
    }
}
