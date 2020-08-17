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
    /**
     * @var UserCanBypassAccessChecker
     */
    protected $flagChecker;

    /**
     * @internal
     *
     * @param UserCanBypassAccessChecker $flagChecker
     */
    public function __construct(UserCanBypassAccessChecker $flagChecker)
    {
        $this->flagChecker = $flagChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function checkBypassAccess($context): bool
    {
        return $this->flagChecker->checkCondition($context);
    }
}
