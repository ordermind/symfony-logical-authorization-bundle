<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionCheckers\SimpleConditionChecker;

interface SimpleConditionCheckerInterface
{
    /**
     * Gets the name of the condition that this class checks.
     *
     * @return string
     */
    public static function getName(): string;

    /**
     * Checks the condition.
     *
     * @param array $context
     *
     * @return bool
     */
    public function checkCondition(array $context): bool;
}
