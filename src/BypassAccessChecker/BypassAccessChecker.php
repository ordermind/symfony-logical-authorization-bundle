<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\BypassAccessChecker;

use Ordermind\LogicalAuthorizationBundle\PermissionCheckers\SimpleConditionChecker\Checkers\UserCanBypassAccessChecker;
use Ordermind\LogicalPermissions\BypassAccessCheckerInterface;

/**
 * Default bypass access checker.
 */
class BypassAccessChecker implements BypassAccessCheckerInterface
{
    protected UserCanBypassAccessChecker $conditionChecker;

    public function __construct(UserCanBypassAccessChecker $conditionChecker)
    {
        $this->conditionChecker = $conditionChecker;
    }

    /**
     * {@inheritDoc}
     */
    public function checkBypassAccess($context): bool
    {
        return $this->conditionChecker->checkCondition($context);
    }
}
