<?php

namespace Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag;

use Ordermind\LogicalAuthorizationBundle\PermissionTypes\PermissionTypeInterface;
use Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag\Flags\FlagInterface;

interface FlagManagerInterface extends PermissionTypeInterface {

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
  public function removeFlag($name);

  /**
   * Gets all registered flags
   *
   * @return array flags
   */
  public function getFlags();
}
