<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

use Ordermind\LogicalAuthorizationBundle\Services\LogicalPermissionsManagerInterface;
use Ordermind\LogicalAuthorizationBundle\Services\PermissionTreeManagerInterface;
use Ordermind\LogicalAuthorizationBundle\Event\AddPermissionsEvent;

class PermissionTreeManager implements PermissionTreeManagerInterface {
  protected $tree;
  protected $appDir;
  protected $permissionKeys;
  protected $dispatcher;
  const FILENAME = 'logical_authorization.yml';

  public function __construct($appDir, LogicalPermissionsManagerInterface $lpManager, EventDispatcherInterface $dispatcher) {
    $this->appDir = $appDir;
    $this->dispatcher = $dispatcher;
    $this->permissionKeys = $lpManager->getValidPermissionKeys();
  }

  public function getTree() {
    if(!is_null($this->tree)) return $this->tree;

    $this->tree = $this->findPermissions();

    return $this->tree;
  }

  protected function findPermissions() {
    $event = new AddPermissionsEvent($this->permissionKeys);
    $this->dispatcher->dispatch('logical_authorization.add_permissions', $event);

    return $event->getTree();
  }
}
