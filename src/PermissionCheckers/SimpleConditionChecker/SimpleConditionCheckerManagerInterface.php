<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionCheckers\SimpleConditionChecker;

use Ordermind\LogicalPermissions\PermissionCheckerInterface;

interface SimpleConditionCheckerManagerInterface extends PermissionCheckerInterface
{
    /**
     * Registers a condition.
     */
    public function addCondition(SimpleConditionCheckerInterface $condition);

    /**
     * Unregisters a condition.
     */
    public function removeCondition(string $name);

    /**
     * Gets all registered conditions.
     */
    public function getConditions(): array;

    /**
     * Checks a simple condition in a given context.
     *
     * @param string $name    The name of the condition to evaluate
     * @param object $context The context for evaluating the condition. For more specific information, check the
     *                        documentation for the condition you want to evaluate.
     */
    public function checkPermission(string $name, $context): bool;
}
