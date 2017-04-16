<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Psr\Cache\CacheItemPoolInterface;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

use Ordermind\LogicalAuthorizationBundle\Services\LogicalPermissionsManagerInterface;
use Ordermind\LogicalAuthorizationBundle\Services\PermissionTreeManagerInterface;
use Ordermind\LogicalAuthorizationBundle\Event\AddPermissionsEvent;

class PermissionTreeManager implements PermissionTreeManagerInterface {
  protected $appDir;
  protected $permissionKeys;
  protected $dispatcher;
  protected $tree;
  protected $cache;

  public function __construct($appDir, LogicalPermissionsManagerInterface $lpManager, EventDispatcherInterface $dispatcher, CacheItemPoolInterface $cache) {
    $this->appDir = $appDir;
    $this->dispatcher = $dispatcher;
    $this->permissionKeys = $lpManager->getValidPermissionKeys();
    $this->cache = $cache;
  }

  public function getTree($reset = false) {
    if(!$reset && !is_null($this->tree)) {
      return $this->tree;
    }

    if(!$reset && !is_null($tree = $this->loadTreeFromCache())) {
      $this->tree = $tree;
      return $this->tree;
    }

    $tree = $this->loadTreeFromEvent();
    $this->saveTreeToCache($tree);
    $this->tree = $tree;

    return $this->tree;
  }

  protected function loadTreeFromCache() {
    $cachedTree = $this->cache->getItem('ordermind.logical_authorization.permissions');
    if($cachedTree->isHit()) {
      return $cachedTree->get();
    }

    return null;
  }

  protected function saveTreeToCache(array $tree) {
    $cachedTree = $this->cache->getItem('ordermind.logical_authorization.permissions');
    $cachedTree->set($tree);
    $this->cache->save($cachedTree);
  }

  protected function loadTreeFromEvent() {
    $event = new AddPermissionsEvent($this->permissionKeys);
    $this->dispatcher->dispatch('logical_authorization.add_permissions', $event);

    return $event->getTree();
  }
}
