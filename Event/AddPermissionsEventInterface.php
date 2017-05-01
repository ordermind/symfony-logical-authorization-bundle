<?php

namespace Ordermind\LogicalAuthorizationBundle\Event;

interface AddPermissionsEventInterface {

  /**
   * Gets the permission tree
   *
   * @return array The permission tree
   */
  public function getTree();

  /**
   * Inserts a new permission tree and merges it into the existing tree, making it possible to override permissions.
   *
   * @param array $tree The new permission tree
   */
  public function insertTree($tree);
}
