<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag;

use Ordermind\LogicalAuthorizationBundle\PermissionTypes\PermissionTypeInterface;
use Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag\Flags\FlagInterface;

/**
 * Permission type for checking a flag.
 */
interface FlagManagerInterface extends PermissionTypeInterface
{

  /**
   * Adds a flag to the collection of registered flags
   *
   * @param FlagInterface $flag The flag to add
   */
    public function addFlag(FlagInterface $flag);

  /**
   * Removes a flag from the collection of registered flags
   *
   * @param string $name The name of the flag that should be removed
   */
    public function removeFlag(string $name);

  /**
   * Gets all registered flags
   *
   * @return array flags
   */
    public function getFlags(): array;

  /**
   * Checks if a flag is switched on in a given context
   *
   * @param string $name    The name of the flag to evaluate
   * @param array  $context The context for evaluating the flag. For more specific information, check the documentation for the flag you want to evaluate.
   *
   * @return bool TRUE if the flag is switched on or FALSE if the flag is switched off
   */
    public function checkPermission(string $name, array $context): bool;
}
