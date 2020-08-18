<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Test\Fixtures\PermissionCheckers;

use Ordermind\LogicalAuthorizationBundle\PermissionCheckers\SimpleConditionChecker\SimpleConditionCheckerInterface;

class TestConditionChecker implements SimpleConditionCheckerInterface
{
    public static function getName(): string
    {
        return 'always_true';
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function checkCondition(array $context): bool
    {
        return true;
    }
}
