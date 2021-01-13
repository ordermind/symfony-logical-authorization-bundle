<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionCheckers\SimpleConditionChecker;

interface SimpleConditionCheckerInterface
{
    /**
     * Gets the name of the condition that this class checks.
     */
    public function getName(): string;

    /**
     * Checks the condition.
     */
    public function checkCondition(object $context): bool;
}
