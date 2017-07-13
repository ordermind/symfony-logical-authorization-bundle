<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

interface PermissionTreeBuilderInterface {

  /**
   * Gets the full permission tree
   *
   * To collect the tree, this method fires the event 'logauth.add_permissions' and passes Ordermind\LogicalAuthorizationBundle\Event\AddPermissionsEvent. It is then sorted by key in alphabetical order and cached with Symfony's Cache Component.
   *
   * @param bool $reset (optional) Set this to TRUE if you want to bypass caches.
   * @param bool $debug (optional) If you set this to TRUE you will get an additional key in the tree called 'fetch', which tells you how the tree was fetched.
   *
   * @return array The permission tree
   */
  public function getTree($reset = false, $debug = false);
}