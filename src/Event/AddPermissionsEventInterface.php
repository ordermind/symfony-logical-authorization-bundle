<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Event;

/**
 * Event for adding permissions
 */
interface AddPermissionsEventInterface
{

  /**
   * Gets the permission tree
   *
   * @return array The permission tree
   */
    public function getTree(): array;

    /**
     * Inserts a new permission tree and merges it into the existing tree, making it possible to override permissions.
     *
     * @param array $tree The new permission tree
     */
    public function insertTree(array $tree);
}
