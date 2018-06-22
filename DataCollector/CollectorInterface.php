<?php

namespace Ordermind\LogicalAuthorizationBundle\DataCollector;

use Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;

interface CollectorInterface extends LateDataCollectorInterface {
  /**
   * Gets the full permission tree.
   *
   * @return array The permission tree
   */
  public function getPermissionTree();

  /**
   * Gets the log items that have been collected
   *
   * @return array The collected log items
   */
  public function getLog();

  /**
   * Adds a permission check to the log
   *
   * @param bool $access TRUE if access was granted of FALSE if it was denied
   * @param string $type The type of item that was the subject of the permission check, for example "route", "model" or "field".
   * @param mixed $item The item that was the subject of the permission check, for example a route name
   * @param mixed $user The current user
   * @param mixed $permissions The permissions that were evaluated
   * @param array $context The context of the evaluation
   * @param string $message (optional) A message to display in the log
   */
  public function addPermissionCheck($access, $type, $item, $user, $permissions, $context, $message = '');
}
