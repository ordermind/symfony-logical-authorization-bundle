<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag\Exceptions;

/**
 * Thrown during attempted uses of flags that are not registered.
 */
class FlagNotRegisteredException extends \InvalidArgumentException
{

}
