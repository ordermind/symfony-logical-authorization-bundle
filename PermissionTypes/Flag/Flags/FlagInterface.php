<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag\Flags;

interface FlagInterface {

  /**
   * Gets the name of the flag.
   *
   * @return string The flag name
   */
  public function getName(): string;

  /**
   * Checks if this flag is on or off in the current context.
   *
   * @param array $context The context for evaluating the flag
   *
   * @return bool TRUE if the flag is switched on or FALSE if the flag is switched off
   */
  public function checkFlag(array $context): bool;
}

