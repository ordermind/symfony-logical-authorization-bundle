<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionCheckers\SimpleConditionChecker\Exceptions;

use InvalidArgumentException;

/**
 * Thrown during attempted uses of a simple condition checker that is not registered.
 */
class SimpleConditionCheckerNotRegisteredException extends InvalidArgumentException
{
}
