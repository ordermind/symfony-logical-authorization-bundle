<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionTypes;

/**
 * Implement this interface in your permission type class.
 */
interface PermissionTypeInterface
{

  /**
   * Gets the name of the permission type
   *
   * @return string $name The permission type's name
   */
    public function getName(): string;

    /**
     * Checks if access should be granted for this permission in a given context
     *
     * @param string $permission The name of the permission to check, for example a certain role or flag
     * @param array  $context    The context for evaluating the permission
     *
     * @return bool TRUE if access is granted or FALSE if access is not granted
     */
    public function checkPermission(string $permission, array $context): bool;
}
